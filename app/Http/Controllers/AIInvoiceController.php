<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\VashiMarketBill;
use App\Models\VashiMarketBillProduct;
use Illuminate\Support\Facades\DB;

class AIInvoiceController extends Controller
{
    // Page load
    public function index()
    {
        return view('admin.aiInvoiceUpload');
    }

    // Process Image
    public function process(Request $request)
    {
        $image = $request->file('image');

        // STEP 1: OCR
        $ocr = Http::attach(
            'image',
            file_get_contents($image),
            $image->getClientOriginalName()
        )->post('http://localhost:5000/extract-text');

        $text = $ocr->json()['text'];

        // STEP 2: LLaMA
        $prompt = "
Extract structured invoice JSON.

Fields:
party_name, bill_no, date

Items:
name, bags, quantity, rate, amount

Return ONLY JSON.

Text:
$text
";

        $llama = Http::post('http://localhost:11434/api/generate', [
            "model" => "llama3",
            "prompt" => $prompt,
            "stream" => false
        ]);

        $response = $llama->json()['response'];

        return response()->json([
            'data' => json_decode($response, true)
        ]);
    }

    // Save to DB
    public function save(Request $request)
    {
        $data = $request->all();

        DB::beginTransaction();

        try {
            $bill = VashiMarketBill::create([
                'bill_no' => $data['bill_no'],
                'party_name' => $data['party_name'],
                'bill_date' => $data['date'],
                'total_bill_amount' => $data['total'] ?? 0
            ]);

            foreach ($data['items'] as $item) {
                VashiMarketBillProduct::create([
                    'vashi_market_bill_id' => $bill->id,
                    'product_name' => $item['name'],
                    'num_bags' => $item['bags'] ?? 0,
                    'total_kg' => $item['quantity'] ?? 0,
                    'rate' => $item['rate'] ?? 0,
                    'product_amount' => $item['amount'] ?? 0,
                ]);
            }

            DB::commit();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()]);
        }
    }
}