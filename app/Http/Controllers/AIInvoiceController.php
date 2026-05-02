<?php

namespace App\Http\Controllers;

use App\Models\VashiMarketBill;
use App\Models\VashiMarketBillProduct;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class AIInvoiceController extends Controller
{
    public function index()
    {
        return view('admin.aiInvoiceUpload');
    }

    public function process(Request $request)
    {
        $maxExec = max(60, (int) config('services.ai_invoice.max_execution_seconds', 360));
        if (function_exists('set_time_limit')) {
            @set_time_limit($maxExec);
        }

        $validator = Validator::make($request->all(), [
            'image' => 'required|file|image|max:15360',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Please upload a valid image (max 15 MB).',
                'errors' => $validator->errors(),
            ], 422);
        }

        $image = $request->file('image');
        $ocrBase = rtrim(config('services.ai_invoice.ocr_url', env('OCR_SERVICE_URL', 'http://127.0.0.1:5000')), '/');
        $ocrUrl = $ocrBase.'/extract-text';

        try {
            $ocr = Http::timeout(120)
                ->attach('image', file_get_contents($image->getRealPath()), $image->getClientOriginalName())
                ->post($ocrUrl);
        } catch (\Throwable $e) {
            $isPortClosed = $e instanceof ConnectionException
                || str_contains($e->getMessage(), 'cURL error 7')
                || str_contains($e->getMessage(), 'Failed to connect');

            return response()->json([
                'message' => $isPortClosed
                    ? 'The OCR service is not running: nothing is accepting HTTP connections on port 5000.'
                    : 'OCR service unreachable. Check the OCR service URL and network, then try again.',
                'hint' => $isPortClosed
                    ? 'Start PaddleOCR: in folder `ai-ocr-service` run `python app.py` (or use `start-ocr.bat`) until the console shows the server listening on port 5000.'
                    : null,
                'detail' => config('app.debug') ? $e->getMessage() : null,
            ], 502);
        }

        if (! $ocr->successful()) {
            $ocrFailBody = $ocr->json();
            if (is_array($ocrFailBody) && ! empty($ocrFailBody['error'])) {
                return response()->json([
                    'message' => 'OCR service error: '.($ocrFailBody['error'] ?? 'unknown'),
                    'hint' => 'If you see a NumPy ABI error, run: pip install "numpy<2" then restart the OCR service.',
                    'detail' => $ocrFailBody['detail'] ?? $ocr->body(),
                ], 502);
            }

            return response()->json([
                'message' => 'OCR request failed.',
                'detail' => $ocr->body(),
            ], 502);
        }

        $ocrJson = $ocr->json();
        if (is_array($ocrJson) && ! empty($ocrJson['error'])) {
            return response()->json([
                'message' => 'OCR failed: '.($ocrJson['error'] ?? 'unknown error'),
                'detail' => $ocrJson['detail'] ?? null,
            ], 502);
        }

        $text = is_array($ocrJson) ? trim((string) ($ocrJson['text'] ?? '')) : '';

        if ($text === '') {
            return response()->json([
                'message' => 'No text could be read from the image. Try a clearer photo.',
            ], 422);
        }

        $prompt = $this->buildLlamaPrompt($text);

        $ollamaUrl = config('services.ai_invoice.ollama_url', env('OLLAMA_URL', 'http://127.0.0.1:11434/api/generate'));
        $model = config('services.ai_invoice.ollama_model', env('OLLAMA_MODEL', 'llama3'));

        $payload = [
            'model' => $model,
            'prompt' => $prompt,
            'stream' => false,
            'format' => 'json',
        ];

        try {
            $llama = Http::timeout(180)->post($ollamaUrl, $payload);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Ollama unreachable. Ensure Ollama is running and the model is pulled.',
                'detail' => config('app.debug') ? $e->getMessage() : null,
            ], 502);
        }

        if (! $llama->successful()) {
            return response()->json([
                'message' => 'Ollama request failed.',
                'detail' => $llama->body(),
            ], 502);
        }

        $body = $llama->json();
        $rawResponse = is_array($body) ? (string) ($body['response'] ?? '') : $llama->body();
        $data = $this->parseLlamaJson($rawResponse);

        if ($data === null) {
            return response()->json([
                'message' => 'Could not parse invoice JSON from the model. Try again or edit manually after a partial result.',
                'raw_response' => config('app.debug') ? $rawResponse : null,
                'ocr_text' => config('app.debug') ? $text : null,
            ], 422);
        }

        $data = $this->normalizeInvoicePayload($data);

        return response()->json([
            'data' => $data,
            'ocr_text' => config('app.debug') ? $text : null,
        ]);
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bill_no' => 'required|string|max:255|unique:vashi_market_bills,bill_no',
            'party_name' => 'required|string|max:255',
            'date' => 'required|string',
            'total' => 'nullable|numeric',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.bags' => 'nullable|numeric',
            'items.*.quantity' => 'nullable|numeric',
            'items.*.rate' => 'nullable|numeric',
            'items.*.amount' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $billDate = Carbon::parse($request->input('date'))->format('Y-m-d');
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Invalid bill date. Use a recognizable date format.'], 422);
        }

        $receivedDate = $billDate;
        $total = $request->input('total');
        if ($total === null || $total === '') {
            $total = collect($request->input('items', []))->sum(function ($item) {
                return (float) ($item['amount'] ?? 0);
            });
        }

        DB::beginTransaction();

        try {
            $bill = VashiMarketBill::create([
                'bill_no' => $request->input('bill_no'),
                'party_name' => $request->input('party_name'),
                'bill_date' => $billDate,
                'received_date' => $receivedDate,
                'dalal' => '-',
                'transport_name' => '-',
                'total_bill_amount' => $total,
            ]);

            foreach ($request->input('items', []) as $item) {
                $bags = (int) round((float) ($item['bags'] ?? 0));
                $kg = (float) ($item['quantity'] ?? 0);
                $bagSize = $bags > 0 ? round($kg / $bags, 2) : 0;

                VashiMarketBillProduct::create([
                    'vashi_market_bill_id' => $bill->id,
                    'product_name' => $item['name'],
                    'brand_name' => null,
                    'num_bags' => $bags,
                    'bag_size' => $bagSize,
                    'total_kg' => $kg,
                    'rate' => (float) ($item['rate'] ?? 0),
                    'product_amount' => (float) ($item['amount'] ?? 0),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'redirect' => route('vashi-market.index'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Could not save the bill.',
                'detail' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    private function buildLlamaPrompt(string $ocrText): string
    {
        return <<<'PROMPT'
Return ONLY valid JSON in this format:

{
"party_name": "",
"bill_no": "",
"date": "",
"items": [
{
"name": "",
"bags": 0,
"quantity": 0,
"rate": 0,
"amount": 0
}
],
"total": 0
}

Rules:

* quantity = weight in KG
* bags = number of bags
* Extract multiple products properly
* If field missing, return empty or 0
* Do NOT return explanation

OCR TEXT:
PROMPT
            ."\n".$ocrText;
    }

    private function parseLlamaJson(string $raw): ?array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/i', $raw, $m)) {
            $raw = trim($m[1]);
        }

        $start = strpos($raw, '{');
        $end = strrpos($raw, '}');
        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        $json = substr($raw, $start, $end - $start + 1);
        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function normalizeInvoicePayload(array $data): array
    {
        $items = [];
        $rawItems = $data['items'] ?? [];
        if (! is_array($rawItems)) {
            $rawItems = [];
        }

        foreach ($rawItems as $row) {
            if (! is_array($row)) {
                continue;
            }
            $items[] = [
                'name' => (string) ($row['name'] ?? ''),
                'bags' => (float) ($row['bags'] ?? 0),
                'quantity' => (float) ($row['quantity'] ?? 0),
                'rate' => (float) ($row['rate'] ?? 0),
                'amount' => (float) ($row['amount'] ?? 0),
            ];
        }

        if ($items === []) {
            $items[] = [
                'name' => '',
                'bags' => 0,
                'quantity' => 0,
                'rate' => 0,
                'amount' => 0,
            ];
        }

        return [
            'party_name' => (string) ($data['party_name'] ?? ''),
            'bill_no' => (string) ($data['bill_no'] ?? ''),
            'date' => (string) ($data['date'] ?? ''),
            'items' => $items,
            'total' => (float) ($data['total'] ?? 0),
        ];
    }
}
