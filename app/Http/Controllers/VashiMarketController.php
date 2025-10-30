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
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = VashiMarketBill::with('products')->latest();


        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('party_name', 'like', "%{$searchTerm}%")
                    ->orWhere('bill_no', 'like', "%{$searchTerm}%")
                    ->orWhere('bill_date', 'like', "%{$searchTerm}%")
                    ->orWhereHas('products', function ($productQuery) use ($searchTerm) {
                        $productQuery->where('product_name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('bill_date', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('bill_date', '<=', $request->input('end_date'));
        }
        $bills = $query->get();
        return view('admin.vashiMarketBillList', compact('bills'));
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
                    'paid_amount'
                ]);
                $billData['is_paid'] = $request->has('is_paid');

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
                'paid_amount'
            ]) + ['is_paid' => $request->has('is_paid')]);

            $bill->products()->delete(); // Remove old
            foreach ($request->products as $product) {
                $bill->products()->create($product);
            }
        });

        return redirect()->route('vashi-market.showBillDetails', $bill->id)->with('success', 'Bill updated successfully.');
    }

    public function showBillDetails(VashiMarketBill $vashiMarketBill)
    {
        // Eager load the products for the specific bill
        $vashiMarketBill->load('products');

        return view('admin.showVashiMarketBill', ['bill' => $vashiMarketBill]);
    }
}