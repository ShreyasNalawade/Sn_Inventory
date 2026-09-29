<?php

namespace App\Http\Controllers;

use App\Models\VashiMarketBill;
use App\Models\VashiMarketBillProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VashiMarketController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.addVashiMarketBill');
    }

    /**
     * Distinct party, product, and brand names for the bill form suggestions.
     */
    public function suggestions(Request $request)
    {
        $fields = [
            'party_name' => [VashiMarketBill::class, 'party_name'],
            'product_name' => [VashiMarketBillProduct::class, 'product_name'],
            'brand_name' => [VashiMarketBillProduct::class, 'brand_name'],
        ];

        $field = (string) $request->query('field', '');
        $term = trim((string) $request->query('q', ''));

        if (! isset($fields[$field]) || $term === '') {
            return response()->json(['items' => []]);
        }

        [$model, $column] = $fields[$field];
        $safeTerm = addcslashes(mb_substr($term, 0, 100), '\\%_');

        $items = $model::query()
            ->where($column, 'like', '%'.$safeTerm.'%')
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderByRaw("CASE WHEN {$column} LIKE ? THEN 0 ELSE 1 END", [$safeTerm.'%'])
            ->orderBy($column)
            ->limit(15)
            ->pluck($column)
            ->values();

        return response()->json(['items' => $items]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $searchTerm = trim((string) $request->input('search', ''));
        $searchDate = null;

        // The UI displays dates as d/m/Y, while the database stores Y-m-d.
        if (preg_match('/^(\d{2})[\/-](\d{2})[\/-](\d{4})$/', $searchTerm, $matches)
            && checkdate((int) $matches[2], (int) $matches[1], (int) $matches[3])) {
            $searchDate = "{$matches[3]}-{$matches[2]}-{$matches[1]}";
        }

        $query = VashiMarketBill::query()
            ->select([
                'id',
                'bill_date',
                'party_name',
                'bill_no',
                'is_paid',
                'paid_amount',
                'total_bill_amount',
                'payment_difference',
            ])
            ->with([
                'products:id,vashi_market_bill_id,product_name',
            ])
            ->orderByDesc('id');

        if ($searchTerm !== '') {
            $query->where(function ($q) use ($searchTerm, $searchDate) {
                $q->where('party_name', 'like', "%{$searchTerm}%")
                    ->orWhere('bill_no', 'like', "%{$searchTerm}%")
                    ->orWhere('bill_date', 'like', "%{$searchTerm}%")
                    ->orWhereHas('products', function ($productQuery) use ($searchTerm) {
                        $productQuery->where('product_name', 'like', "%{$searchTerm}%");
                    });

                if ($searchDate) {
                    $q->orWhereDate('bill_date', $searchDate);
                }
            });
        }

        if ($request->filled('payment_status')) {
            if ($request->input('payment_status') === 'paid') {
                $query->where('is_paid', true);
            } elseif ($request->input('payment_status') === 'unpaid') {
                $query->where('is_paid', false);
            }
        }

        if ($request->filled('start_date')) {
            $query->where('bill_date', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->where('bill_date', '<=', $request->input('end_date'));
        }
        // Cursor pagination avoids the increasingly expensive OFFSET used by
        // normal pagination when this table contains millions of records.
        $bills = $query->cursorPaginate(20, ['*'], 'cursor', $request->input('cursor'));

        if ($request->expectsJson() || $request->ajax()) {
            $nextCursor = $bills->nextCursor();
            $payload = [
                'html' => view('admin.vashiMarketBillCards', compact('bills'))->render(),
                'has_more' => $bills->hasMorePages(),
                'next_cursor' => $nextCursor ? $nextCursor->encode() : null,
            ];

            // Infinite scroll repeats the same filters, so the unpaid totals
            // only need to be calculated for the first page of a filter.
            if (! $request->filled('cursor')) {
                $payload['unpaid_summary'] = $this->unpaidSummaryPayload($request);
            }

            return response()->json($payload);
        }

        $unpaidSummary = $this->unpaidSummaryPayload($request);

        return view('admin.vashiMarketBillList', compact('bills', 'unpaidSummary'));
    }

    /**
     * Unpaid count and amount for the selected bill dates.
     * With no dates, this is every unpaid bill so far.
     *
     * @return array{count: string, amount: string, note: string}
     */
    private function unpaidSummaryPayload(Request $request): array
    {
        $query = VashiMarketBill::query()->where('is_paid', false);
        $startDate = $this->filterDate($request->input('start_date'));
        $endDate = $this->filterDate($request->input('end_date'));

        if ($startDate) {
            $query->where('bill_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('bill_date', '<=', $endDate);
        }

        $summary = $query
            ->selectRaw('COUNT(*) as bill_count, COALESCE(SUM(total_bill_amount), 0) as bill_amount')
            ->first();

        return [
            'count' => number_format((int) $summary->bill_count),
            'amount' => '₹'.number_format((float) $summary->bill_amount, 2),
            'note' => $this->unpaidSummaryNote($startDate, $endDate),
        ];
    }

    private function unpaidSummaryNote(?string $startDate, ?string $endDate): string
    {
        $from = $startDate ? date('d/m/Y', strtotime($startDate)) : null;
        $to = $endDate ? date('d/m/Y', strtotime($endDate)) : null;

        if ($from && $to) {
            return "Unpaid bills from {$from} to {$to}";
        }

        if ($from) {
            return "Unpaid bills from {$from}";
        }

        if ($to) {
            return "Unpaid bills up to {$to}";
        }

        return '';
    }

    private function filterDate(mixed $value): ?string
    {
        $value = trim((string) $value);

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        [$year, $month, $day] = array_map('intval', explode('-', $value));

        return checkdate($month, $day, $year) ? $value : null;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bill_date' => 'required|date',
            'received_date' => 'required|date',
            'bill_no' => 'required|string|unique:vashi_market_bills,bill_no',
            'party_name' => 'required|string|max:255',
            'dalal' => 'required|string|max:255',
            'transport_name' => 'required|string|max:255',
            'total_bill_amount' => 'required|numeric',
            'is_paid' => 'sometimes|boolean',
            'payment_type' => 'nullable|string',
            'transaction_id' => 'nullable|string',
            'cheque_no' => 'nullable|string',
            'receipt_no' => 'nullable|string',
            'paid_date' => 'nullable|date',
            'paid_amount' => 'nullable|numeric',
            'payment_difference' => 'nullable|numeric',
            'products' => 'required|array',
            'products.*.product_name' => 'required|string',
            'products.*.brand_name' => 'nullable|string',
            'products.*.num_bags' => 'required|integer',
            'products.*.bag_size' => 'required|numeric',
            'products.*.total_kg' => 'required|numeric',
            'products.*.rate' => 'required|numeric',
            'products.*.product_amount' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
            // return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::transaction(function () use ($request) {
                $billData = $request->only([
                    'bill_date',
                    'received_date',
                    'bill_no',
                    'party_name',
                    'dalal',
                    'transport_name',
                    'total_bill_amount',
                    'payment_type',
                    'transaction_id',
                    'cheque_no',
                    'receipt_no',
                    'paid_date',
                    'paid_amount',
                    'payment_difference',
                ]);
                $billData['is_paid'] = $request->has('is_paid');
                $billData['payment_difference'] = $billData['is_paid']
                    ? ($request->input('payment_difference') ?? 0)
                    : null;

                $bill = VashiMarketBill::create($billData);

                foreach ($request->products as $productData) {
                    $bill->products()->create($productData);
                }
            });

            return back()->with('success', 'Bill added successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while adding the bill.')->withInput();
        }
    }
    // NEW METHOD: Show the payment update form
    public function showPaymentForm(VashiMarketBill $vashiMarketBill)
    {
        // return view('admin.payment', ['bill' => $vashiMarketBill]);
    }


    public function editVashiBill($id)
    {
        $bill = VashiMarketBill::with('products')->findOrFail($id);
        return view('admin.editVashiBill', compact('bill'));
    }
    public function updateVashiBill(Request $request, $id)
    {
        $bill = VashiMarketBill::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'bill_date' => 'required|date',
            'received_date' => 'required|date',
            'party_name' => 'required|string|max:255',
            'dalal' => 'required|string|max:255',
            'transport_name' => 'required|string|max:255',
            'total_bill_amount' => 'required|numeric',
            'is_paid' => 'sometimes|boolean',
            'payment_type' => 'nullable|string',
            'transaction_id' => 'nullable|string',
            'cheque_no' => 'nullable|string',
            'receipt_no' => 'nullable|string',
            'paid_date' => 'nullable|date',
            'paid_amount' => 'nullable|numeric',
            'payment_difference' => 'nullable|numeric',
            'products' => 'required|array',
            'products.*.product_name' => 'required|string',
            'products.*.brand_name' => 'nullable|string',
            'products.*.num_bags' => 'required|integer',
            'products.*.bag_size' => 'required|numeric',
            'products.*.total_kg' => 'required|numeric',
            'products.*.rate' => 'required|numeric',
            'products.*.product_amount' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($bill, $request) {
            $isPaid = $request->has('is_paid');
            $submittedDifference = $request->input('payment_difference');

            if (! $isPaid) {
                $paymentDifference = null;
            } elseif ($submittedDifference === null || $submittedDifference === '') {
                $paymentDifference = round(
                    (float) $request->input('paid_amount') - (float) $request->input('total_bill_amount'),
                    2
                );
            } else {
                $paymentDifference = round((float) $submittedDifference, 2);
            }

            $bill->update($request->only([
                'bill_date',
                'received_date',
                'party_name',
                'dalal',
                'transport_name',
                'total_bill_amount',
                'payment_type',
                'transaction_id',
                'cheque_no',
                'receipt_no',
                'paid_date',
                'paid_amount',
            ]) + [
                'is_paid' => $isPaid,
                'payment_difference' => $paymentDifference,
            ]);

            $bill->products()->delete(); // Remove old
            foreach ($request->products as $product) {
                $bill->products()->create($product);
            }
        });

        return redirect()->route('vashi-market.showBillDetails', $bill->id)->with('success', 'Bill updated successfully.');
    }

    public function showBillDetails(VashiMarketBill $vashiMarketBill)
    {
        // Eager load the products and any group-payment allocations for the specific bill
        $vashiMarketBill->load([
            'products',
            'paymentAllocations.payment.allocations.bill:id,bill_no,total_bill_amount',
        ]);

        return view('admin.showVashiMarketBill', ['bill' => $vashiMarketBill]);
    }
}