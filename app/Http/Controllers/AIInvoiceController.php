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
            'items.*.brand' => 'nullable|string|max:255',
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
                    'brand_name' => isset($item['brand']) && $item['brand'] !== '' ? $item['brand'] : null,
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
        $buyers = config('services.ai_invoice.buyer_aliases', []);
        $buyerBlock = $buyers === []
            ? 'Sandip Oil Depo / Sandeep Oil Depo (and similar spellings)'
            : implode(' | ', $buyers);

        return <<<PROMPT
You extract data from noisy OCR of Indian trade bills (APMC, rice/grain merchants, etc.).
Return ONLY valid JSON. No markdown, no explanation.

Schema (match these keys exactly):
{
  "party_name": "",
  "bill_no": "",
  "date": "",
  "items": [
    {
      "name": "",
      "brand": "",
      "bags": 0,
      "quantity": 0,
      "rate": 0,
      "amount": 0
    }
  ],
  "total": 0
}

=== party_name (CRITICAL) ===
- "party_name" MUST be the SELLER / SUPPLIER / ISSUER of the bill — the company whose letterhead or legal name appears at the TOP as the issuer (e.g. "RAJNI TRADING CO.", "Rani Trading Co.").
- It MUST NOT be the CUSTOMER / CONSIGNEE / BUYER (often labeled Party Name, M/s, Bill To, Sold To, Delivered To, or in a box below the header).
- NEVER set party_name to our shop (the buyer). These names are FORBIDDEN for party_name: {$buyerBlock}
- If OCR shows both issuer and customer, always pick the issuer for party_name.

=== bill_no, date, total ===
- bill_no: invoice / bill number (e.g. 25001), not old "previous dues" bill numbers unless clearly the main invoice number.
- date: invoice date as on the bill (keep format from OCR if unclear, else DD/MM/YY or YYYY-MM-DD).
- total: final payable "Net Bill" / grand total (include APMC/fees only if that is the printed net total). Prefer the bottom net figure over line subtotal if both exist.

=== items[] (one object per product ROW — do not merge or swap rows) ===
- bags: integer count of bags for that line.
- quantity: TOTAL net weight for that line in KILOGRAMS (use "Net Wt", "Net Weight", "Qty KG" — NOT bag size alone). Example: 6 bags × 30 kg bag = 180 → quantity 180.
- amount: line total amount in rupees for that row (must match the Amount column for that row, not another row).
- rate: RUPEES PER KILOGRAM for that line. Compute as rate = amount / quantity when quantity > 0.
  If the bill only shows "Rate/Qtl" or per quintal (100 kg), convert: rate_per_kg = rate_qtl / 100, then you may set amount consistent with quantity * rate_per_kg.
  Never put the per-quintal number into "rate" without dividing by 100.
- brand: trade/brand prefix from the item description (e.g. "SUDHA-RAS", "SILVER S") before the commodity name. If only one word, brand can be empty and put full text in name.
- name: product / commodity description WITHOUT repeating the brand if split (e.g. "WHEAT LOKVAN 30 KG" or "WHEAT LOKVAN"). Drop decorative asterisks.

Sanity: For each item, quantity and amount must correspond to the same table row; bags * typical bag size should be near quantity when bag size is printed.

OCR TEXT:
{$ocrText}
PROMPT;
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
            $qty = (float) ($row['quantity'] ?? 0);
            $amt = (float) ($row['amount'] ?? 0);
            $rate = (float) ($row['rate'] ?? 0);
            if ($qty > 0 && $amt > 0) {
                $rate = round($amt / $qty, 4);
            }
            $brand = trim((string) ($row['brand'] ?? ''));
            if ($brand === '' && isset($row['brand_name'])) {
                $brand = trim((string) $row['brand_name']);
            }
            $items[] = [
                'name' => (string) ($row['name'] ?? ''),
                'brand' => $brand,
                'bags' => (float) ($row['bags'] ?? 0),
                'quantity' => $qty,
                'rate' => $rate,
                'amount' => $amt,
            ];
        }

        if ($items === []) {
            $items[] = [
                'name' => '',
                'brand' => '',
                'bags' => 0,
                'quantity' => 0,
                'rate' => 0,
                'amount' => 0,
            ];
        }

        $party = trim((string) ($data['party_name'] ?? ''));
        $party = $this->stripBuyerFromPartyName($party);

        return [
            'party_name' => $party,
            'bill_no' => (string) ($data['bill_no'] ?? ''),
            'date' => (string) ($data['date'] ?? ''),
            'items' => $items,
            'total' => (float) ($data['total'] ?? 0),
        ];
    }

    /**
     * If the model still returns our buyer as party_name, clear it so the user must pick the seller.
     */
    private function stripBuyerFromPartyName(string $party): string
    {
        if ($party === '') {
            return '';
        }
        $normalized = mb_strtolower(preg_replace('/\s+/u', ' ', $party));
        foreach (config('services.ai_invoice.buyer_aliases', []) as $alias) {
            $a = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $alias)));
            if ($a === '' || mb_strlen($a) < 4) {
                continue;
            }
            if (str_contains($normalized, $a) || str_contains($a, $normalized)) {
                return '';
            }
        }

        return $party;
    }
}
