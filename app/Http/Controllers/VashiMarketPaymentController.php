<?php

namespace App\Http\Controllers;

use App\Models\VashiMarketBill;
use App\Models\VashiMarketPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class VashiMarketPaymentController extends Controller
{
    /**
     * Show the multi-bill payment form.
     */
    public function create(Request $request)
    {
        $partyName = trim((string) $request->input('party_name', ''));
        $parties = VashiMarketBill::query()
            ->where('is_paid', false)
            ->select('party_name')
            ->distinct()
            ->orderBy('party_name')
            ->pluck('party_name');

        $bills = collect();

        if ($partyName !== '') {
            $bills = VashiMarketBill::query()
                ->with(['products:id,vashi_market_bill_id,product_name'])
                ->where('is_paid', false)
                ->where('party_name', $partyName)
                ->orderBy('bill_date')
                ->orderBy('id')
                ->get([
                    'id',
                    'bill_no',
                    'bill_date',
                    'party_name',
                    'total_bill_amount',
                ]);
        }

        return view('admin.payMultipleVashiBills', compact('parties', 'partyName', 'bills'));
    }

    /**
     * Fetch unpaid bills for a party (AJAX).
     */
    public function unpaidBills(Request $request)
    {
        $partyName = trim((string) $request->input('party_name', ''));

        if ($partyName === '') {
            return response()->json(['bills' => []]);
        }

        $bills = VashiMarketBill::query()
            ->with(['products:id,vashi_market_bill_id,product_name'])
            ->where('is_paid', false)
            ->where('party_name', $partyName)
            ->orderBy('bill_date')
            ->orderBy('id')
            ->get([
                'id',
                'bill_no',
                'bill_date',
                'party_name',
                'total_bill_amount',
            ])
            ->map(function (VashiMarketBill $bill) {
                return [
                    'id' => $bill->id,
                    'bill_no' => $bill->bill_no,
                    'bill_date' => optional($bill->bill_date)->format('Y-m-d')
                        ?? \Carbon\Carbon::parse($bill->bill_date)->format('Y-m-d'),
                    'bill_date_display' => \Carbon\Carbon::parse($bill->bill_date)->format('d/m/Y'),
                    'party_name' => $bill->party_name,
                    'total_bill_amount' => (float) $bill->total_bill_amount,
                    'products' => $bill->products->pluck('product_name')->implode(', '),
                ];
            });

        return response()->json(['bills' => $bills]);
    }

    /**
     * Store a group payment and safely update only selected unpaid bills.
     * Existing bill rows are never deleted.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'party_name' => 'required|string|max:255',
            'payment_type' => 'required|string|max:255',
            'transaction_id' => 'nullable|string|max:255',
            'cheque_no' => 'nullable|string|max:255',
            'receipt_no' => 'nullable|string|max:255',
            'paid_date' => 'required|date',
            'total_paid_amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
            'bills' => 'required|array|min:1',
            'bills.*.id' => 'required|integer|exists:vashi_market_bills,id',
            'bills.*.allocated_amount' => 'required|numeric|min:0',
            'bills.*.interest_difference' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $partyName = trim($request->input('party_name'));
        $billInputs = collect($request->input('bills', []));
        $billIds = $billInputs->pluck('id')->map(fn ($id) => (int) $id)->unique()->values();
        $totalPaidAmount = round((float) $request->input('total_paid_amount'), 2);
        $allocatedSum = round($billInputs->sum(fn ($row) => (float) $row['allocated_amount']), 2);

        if (abs($allocatedSum - $totalPaidAmount) > 0.05) {
            return back()->withErrors([
                'total_paid_amount' => 'Sum of bill paid amounts must match the total paid amount.',
            ])->withInput();
        }

        try {
            DB::transaction(function () use ($request, $billIds, $billInputs, $partyName, $totalPaidAmount) {
                $bills = VashiMarketBill::query()
                    ->whereIn('id', $billIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                if ($bills->count() !== $billIds->count()) {
                    throw ValidationException::withMessages([
                        'bills' => 'One or more selected bills were not found.',
                    ]);
                }

                foreach ($bills as $bill) {
                    if ($bill->is_paid) {
                        throw ValidationException::withMessages([
                            'bills' => "Bill #{$bill->bill_no} is already paid and was not changed.",
                        ]);
                    }

                    if (strcasecmp(trim($bill->party_name), $partyName) !== 0) {
                        throw ValidationException::withMessages([
                            'bills' => "Bill #{$bill->bill_no} does not belong to party {$partyName}.",
                        ]);
                    }
                }

                $totalBillsAmount = round($bills->sum(fn ($bill) => (float) $bill->total_bill_amount), 2);

                $payment = VashiMarketPayment::create([
                    'party_name' => $partyName,
                    'payment_type' => $request->input('payment_type'),
                    'transaction_id' => $request->input('transaction_id'),
                    'cheque_no' => $request->input('cheque_no'),
                    'receipt_no' => $request->input('receipt_no'),
                    'paid_date' => $request->input('paid_date'),
                    'total_bills_amount' => $totalBillsAmount,
                    'total_paid_amount' => $totalPaidAmount,
                    'total_difference' => round($totalPaidAmount - $totalBillsAmount, 2),
                    'notes' => $request->input('notes'),
                ]);

                foreach ($billInputs as $row) {
                    $bill = $bills[(int) $row['id']];
                    $billAmount = round((float) $bill->total_bill_amount, 2);
                    $allocatedAmount = round((float) $row['allocated_amount'], 2);
                    $interestDifference = round((float) $row['interest_difference'], 2);
                    $expectedInterest = round($allocatedAmount - $billAmount, 2);

                    if (abs($interestDifference - $expectedInterest) > 0.05) {
                        $interestDifference = $expectedInterest;
                    }

                    $interestPercentage = $billAmount > 0
                        ? round((abs($interestDifference) / $billAmount) * 100, 2)
                        : 0;

                    $payment->allocations()->create([
                        'vashi_market_bill_id' => $bill->id,
                        'bill_amount' => $billAmount,
                        'allocated_amount' => $allocatedAmount,
                        'interest_difference' => $interestDifference,
                        'interest_percentage' => $interestPercentage,
                    ]);

                    // Update only payment fields on the selected unpaid bill. Never delete the bill.
                    $bill->update([
                        'is_paid' => true,
                        'payment_type' => $request->input('payment_type'),
                        'transaction_id' => $request->input('transaction_id'),
                        'cheque_no' => $request->input('cheque_no'),
                        'receipt_no' => $request->input('receipt_no'),
                        'paid_date' => $request->input('paid_date'),
                        'paid_amount' => $allocatedAmount,
                        'payment_difference' => $interestDifference,
                    ]);
                }
            });
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            return back()
                ->with('error', 'Unable to save the group payment. No bills were deleted.')
                ->withInput();
        }

        return redirect()
            ->route('vashi-market.index', ['search' => $partyName, 'payment_status' => 'paid'])
            ->with('success', 'Group payment saved. Selected bills were marked paid with split amounts.');
    }
}
