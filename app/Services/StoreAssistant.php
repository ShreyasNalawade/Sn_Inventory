<?php

namespace App\Services;

use App\Models\Product;
use App\Models\VashiMarketBill;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class StoreAssistant
{
    public function reply(string $message): string
    {
        $text = Str::of($message)->lower()->squish()->toString();

        if ($this->isClosing($text)) {
            return 'You are welcome. Ask whenever you need a price or a bill.';
        }

        if ($this->isGreeting($text)) {
            return implode("\n", [
                'Hello. I can look up product prices and Vashi Market bills.',
                '',
                'Ask for a product name, an unpaid bill summary, or a party name.',
            ]);
        }

        if ($this->isHelp($text)) {
            return $this->helpText();
        }

        $billNumber = $this->extractBillNumber($message);
        if ($billNumber !== null) {
            return $this->billByNumber($billNumber);
        }

        $party = $this->extractParty($message);
        if ($party !== null) {
            return $this->billsForParty($party, $this->asksUnpaid($text));
        }

        if ($this->asksUnpaid($text)) {
            return $this->unpaidSummary();
        }

        if ($this->asksPaid($text)) {
            return $this->paidSummary();
        }

        if ($this->asksRecentBills($text)) {
            return $this->recentBills();
        }

        if ($this->asksBillOverview($text)) {
            return $this->billOverview();
        }

        $type = $this->requestedType($text);
        if ($type && preg_match('/\b(how many|count of|number of|total)\b/', $text)) {
            return $this->productCounts($type);
        }

        if ($type && $this->isTypeListRequest($text, $type)) {
            return $this->productsByType($type);
        }

        if (
            (preg_match('/\b(how many|number of|count of)\b/', $text) && preg_match('/\bproducts?\b/', $text))
            || preg_match('/\btotal products?\b|\bproduct count\b/', $text)
        ) {
            return $this->productCounts();
        }

        $productQuery = $this->extractProductQuery($message);
        if ($productQuery !== null) {
            return $this->findProducts($productQuery)
                ?? 'I could not find a product matching "'.Str::limit($productQuery, 80, '').'".';
        }

        if (count(preg_split('/\s+/', trim($message))) <= 8) {
            $direct = $this->findProducts($message);
            if ($direct !== null) {
                return $direct;
            }
        }

        return $this->fallback();
    }

    private function isGreeting(string $text): bool
    {
        return (bool) preg_match('/^(hi|hii+|hello|hey|good (morning|afternoon|evening)|namaste)[!. ]*$/', $text);
    }

    private function isHelp(string $text): bool
    {
        return (bool) preg_match('/\b(help|what can you do|how can you help|what do you know|commands)\b/', $text);
    }

    private function isClosing(string $text): bool
    {
        return (bool) preg_match('/^(thanks|thank you|thankyou|ok|okay|bye|goodbye|got it)[!. ]*$/', $text);
    }

    private function asksUnpaid(string $text): bool
    {
        if (preg_match('/\b(unpaid|outstanding|not paid|amount due)\b/', $text)) {
            return true;
        }

        return (bool) preg_match('/\b(pending|due)\b/', $text)
            && (bool) preg_match('/\b(bill|bills|payment|amount)\b/', $text);
    }

    private function asksPaid(string $text): bool
    {
        if (preg_match('/\bunpaid\b/', $text)) {
            return false;
        }

        return (bool) preg_match('/\b(paid bills|bills that are paid|settled bills)\b/', $text)
            || ((bool) preg_match('/\bpaid\b/', $text) && (bool) preg_match('/\bbills?\b/', $text));
    }

    private function asksRecentBills(string $text): bool
    {
        return (bool) preg_match('/\b(recent|latest|last|newest)\b/', $text)
            && (bool) preg_match('/\bbills?\b/', $text);
    }

    private function asksBillOverview(string $text): bool
    {
        return (bool) preg_match('/\b(how many|total|summary|overview)\b/', $text)
            && (bool) preg_match('/\bbills?\b/', $text);
    }

    private function requestedType(string $text): ?string
    {
        foreach (['grocery', 'oil', 'masala', 'other'] as $type) {
            if (preg_match('/\b'.$type.'\b/', $text)) {
                return $type;
            }
        }

        return null;
    }

    private function isTypeListRequest(string $text, string $type): bool
    {
        return (bool) preg_match(
            '/\b(?:list|show|display|all)\b[^.]*\b'.$type.'\b|\b'.$type.'\s+(?:products?|prices?|items?|list)\b/',
            $text
        );
    }

    private function extractBillNumber(string $message): ?string
    {
        if (preg_match('/\bbill\s*(?:no\.?|number|#)\s*[:#-]?\s*([A-Za-z0-9][A-Za-z0-9\/\-]+)/iu', $message, $matches)) {
            return $matches[1];
        }

        if (preg_match('/\bbill\s+([A-Za-z0-9]*\d[A-Za-z0-9\/\-]+)/iu', $message, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function extractParty(string $message): ?string
    {
        if (! preg_match('/\b(?:bills?\s+(?:for|of|from|by)|party(?:\s+name)?(?:\s+is)?)\s+(.+)$/iu', $message, $matches)) {
            return null;
        }

        $party = trim($matches[1], " \t\n\r\0\x0B?.,!");
        $party = preg_replace('/\s+(bills?|please)$/iu', '', $party) ?? $party;
        $party = trim($party);

        return $party !== '' ? $party : null;
    }

    private function extractProductQuery(string $message): ?string
    {
        $patterns = [
            '/\b(?:what(?:\'s| is) the\s+)?(?:price|rate|cost)\s+(?:of|for)\s+(.+)$/iu',
            '/\b(?:find|search|lookup)\s+(?:for\s+)?(?:a\s+)?(?:product\s+)?(.+)$/iu',
            '/\b(.+?)\s+(?:price|rate|cost)\s*\??$/iu',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                return $this->cleanProductQuery($matches[1]);
            }
        }

        return null;
    }

    private function cleanProductQuery(string $query): ?string
    {
        $query = trim($query, " \t\n\r\0\x0B?.,!");
        $query = preg_replace('/\b(please|thanks|thank you)\b/iu', '', $query) ?? $query;
        $query = trim(preg_replace('/\s+/', ' ', $query) ?? $query);

        return mb_strlen($query) >= 2 ? $query : null;
    }

    private function helpText(): string
    {
        return implode("\n", [
            'I answer from the price list and Vashi Market bills.',
            '',
            'You can ask:',
            '• How many products do we have?',
            '• Price of sunflower oil',
            '• List grocery products',
            '• Show unpaid bills',
            '• Latest Vashi bills',
            '• Bills for a party name',
            '• Bill no 1042',
        ]);
    }

    private function fallback(): string
    {
        return implode("\n", [
            'I could not match that to a product or a bill.',
            '',
            'Try one of these:',
            '• Price of sunflower oil',
            '• List oil products',
            '• Show unpaid bills',
            '• Bills for a party name',
            '• Bill no 1042',
        ]);
    }

    private function productCounts(?string $onlyType = null): string
    {
        $counts = Product::query()
            ->selectRaw('type_product, COUNT(*) as total')
            ->groupBy('type_product')
            ->pluck('total', 'type_product');

        if ($onlyType) {
            $total = (int) ($counts[$onlyType] ?? 0);
            $label = $this->typeLabel($onlyType);

            if ($total === 0) {
                return "There are no {$label} products on the price list.";
            }

            return $label.': '.$total.' '.($total === 1 ? 'product' : 'products').".\n\nSay “list {$onlyType} products” to see them.";
        }

        $order = ['grocery', 'oil', 'masala', 'other'];
        $lines = ['Price list', ''];
        $sum = 0;

        foreach ($order as $type) {
            $total = (int) ($counts[$type] ?? 0);
            $sum += $total;
            $lines[] = $this->typeLabel($type).': '.$total;
        }

        $unknown = (int) $counts->except($order)->sum();
        if ($unknown > 0) {
            $sum += $unknown;
            $lines[] = 'Uncategorised: '.$unknown;
        }

        $lines[] = '';
        $lines[] = 'Total: '.$sum;

        return implode("\n", $lines);
    }

    private function productsByType(string $type): string
    {
        $query = Product::query()->where('type_product', $type);
        $total = (clone $query)->count();
        $label = $this->typeLabel($type);

        if ($total === 0) {
            return "There are no {$label} products on the price list.";
        }

        $products = $query->orderBy('name')->limit(8)->get();
        if ($total === 1) {
            return $this->productDetail($products->first());
        }

        $lines = [$label.' products', '', $total.' products.'];

        if ($total > $products->count()) {
            $lines[] = 'Showing '.$products->count().' of '.$total.'.';
        }

        $lines[] = '';
        foreach ($products as $product) {
            $lines[] = $this->productListLine($product);
        }

        return implode("\n", $lines);
    }

    private function findProducts(string $query): ?string
    {
        $normalized = Str::lower(trim($query));
        if (preg_match('/^(grocery|oil|masala|other)(?:\s+products?)?$/', $normalized, $matches)) {
            return $this->productsByType($matches[1]);
        }

        $words = $this->searchWords($query);
        if ($words === []) {
            return null;
        }

        $builder = Product::query();
        foreach ($words as $word) {
            $like = $this->likeTerm($word);
            $builder->where(function ($inner) use ($like) {
                $inner->where('name', 'like', $like)
                    ->orWhere('brand_name', 'like', $like);
            });
        }

        $total = (clone $builder)->count();
        if ($total === 0) {
            return null;
        }

        $products = $builder->orderBy('name')->limit(8)->get();
        if ($total === 1) {
            return $this->productDetail($products->first());
        }

        $lines = [
            'I found '.$total.' products matching "'.Str::limit($query, 80, '').'".',
        ];

        if ($total > $products->count()) {
            $lines[] = 'Showing '.$products->count().'. Ask for a more specific name to see one product.';
        }

        $lines[] = '';
        foreach ($products as $product) {
            $lines[] = $this->productListLine($product);
        }

        return implode("\n", $lines);
    }

    private function productDetail(Product $product): string
    {
        $title = $product->name;
        if ($product->brand_name) {
            $title .= ' · '.$product->brand_name;
        }

        $lines = [$title, $this->typeLabel($product->type_product), ''];

        if ($product->type_product === 'oil') {
            $sizes = $this->oilSizePrices($product);
            if ($sizes === []) {
                $lines[] = 'No size prices are saved for this oil.';

                return implode("\n", $lines);
            }

            $lines[] = 'Purchase and sale (7% markup, 5% on 15L):';
            foreach ($sizes as $label => $purchase) {
                $sale = round((float) $purchase * $this->oilMarkup($label));
                $lines[] = $label.': purchase '.$this->money($purchase).' · sale '.$this->money($sale);
            }

            return implode("\n", $lines);
        }

        $purchase = (float) $product->purchase_price;
        $lines[] = 'Purchase: '.$this->money($purchase);

        if ($product->type_product === 'grocery') {
            $sale = round($purchase * 1.12, 2);
            $lines[] = 'Sale uses a 12% markup.';
            $lines[] = '1kg: '.$this->money($sale);
            $lines[] = '500g: '.$this->money(round($sale / 2, 2));
            $lines[] = '250g: '.$this->money(round($sale / 4, 2));

            return implode("\n", $lines);
        }

        if ($product->type_product === 'masala') {
            $sale15 = round($purchase * 1.15, 2);
            $sale20 = $purchase * 1.20;
            $lines[] = '1kg and 250g use a 15% markup. 50g and 10g use 20%.';
            $lines[] = '1kg: '.$this->money($sale15);
            $lines[] = '250g: '.$this->money(round($sale15 / 4, 2));
            $lines[] = '50g: '.$this->money(round($sale20 / 20, 2));
            $lines[] = '10g: '.$this->money(round($sale20 / 100, 2));

            return implode("\n", $lines);
        }

        $lines[] = 'Sale: '.$this->money($purchase);

        return implode("\n", $lines);
    }

    private function productListLine(Product $product): string
    {
        $name = $product->name;
        if ($product->brand_name) {
            $name .= ' · '.$product->brand_name;
        }

        return '• '.$name.' — '.$this->priceSnippet($product);
    }

    private function priceSnippet(Product $product): string
    {
        if ($product->type_product === 'oil') {
            $parts = [];
            foreach ($this->oilSizePrices($product) as $label => $purchase) {
                $parts[] = $label.' '.$this->money($purchase);
            }

            if ($parts === []) {
                return 'No size prices saved';
            }

            if (count($parts) > 3) {
                $extra = count($parts) - 3;
                $parts = array_slice($parts, 0, 3);
                $parts[] = '+'.$extra.' sizes';
            }

            return implode(' · ', $parts);
        }

        return 'Purchase '.$this->money($product->purchase_price);
    }

    private function oilSizePrices(Product $product): array
    {
        $sizes = [
            '750ml' => $product->size_750ml,
            '1L' => $product->size_1L,
            '3L' => $product->size_3L,
            '5L' => $product->size_5L,
            '15L tin' => $product->size_15L_tin,
            '15L jar' => $product->size_15L_jar,
        ];

        $filled = [];
        foreach ($sizes as $label => $price) {
            if ($this->hasPrice($price)) {
                $filled[$label] = $price;
            }
        }

        return $filled;
    }

    private function oilMarkup(string $label): float
    {
        return str_starts_with($label, '15L') ? 1.05 : 1.07;
    }

    private function unpaidSummary(): string
    {
        $query = VashiMarketBill::query()->where('is_paid', false);
        $count = (clone $query)->count();

        if ($count === 0) {
            return 'There are no unpaid Vashi Market bills.';
        }

        $amount = (clone $query)->sum('total_bill_amount');
        $bills = $this->latestBills($query, 5);
        $lines = [
            'Unpaid Vashi Market bills',
            '',
            $count.' '.($count === 1 ? 'bill is' : 'bills are').' unpaid.',
            'Outstanding total: '.$this->money($amount),
            '',
            $count > $bills->count() ? 'Latest unpaid bills:' : 'Bills:',
        ];

        foreach ($bills as $bill) {
            $lines[] = $this->billLine($bill);
        }

        return implode("\n", $lines);
    }

    private function paidSummary(): string
    {
        $query = VashiMarketBill::query()->where('is_paid', true);
        $count = (clone $query)->count();

        if ($count === 0) {
            return 'There are no paid Vashi Market bills.';
        }

        $amount = (clone $query)->sum('total_bill_amount');
        $bills = $this->latestBills($query, 5);
        $lines = [
            'Paid Vashi Market bills',
            '',
            $count.' '.($count === 1 ? 'bill is' : 'bills are').' paid.',
            'Paid total: '.$this->money($amount),
            '',
            $count > $bills->count() ? 'Latest paid bills:' : 'Bills:',
        ];

        foreach ($bills as $bill) {
            $lines[] = $this->billLine($bill);
        }

        return implode("\n", $lines);
    }

    private function billOverview(): string
    {
        $rows = VashiMarketBill::query()
            ->selectRaw('is_paid, COUNT(*) as total, COALESCE(SUM(total_bill_amount), 0) as amount')
            ->groupBy('is_paid')
            ->get();

        $paidCount = 0;
        $paidAmount = 0.0;
        $unpaidCount = 0;
        $unpaidAmount = 0.0;

        foreach ($rows as $row) {
            if ($row->is_paid) {
                $paidCount = (int) $row->total;
                $paidAmount = (float) $row->amount;
            } else {
                $unpaidCount = (int) $row->total;
                $unpaidAmount = (float) $row->amount;
            }
        }

        if ($paidCount === 0 && $unpaidCount === 0) {
            return 'There are no Vashi Market bills yet.';
        }

        return implode("\n", [
            'Vashi Market bills',
            '',
            'Unpaid: '.$unpaidCount.' · '.$this->money($unpaidAmount),
            'Paid: '.$paidCount.' · '.$this->money($paidAmount),
        ]);
    }

    private function recentBills(): string
    {
        $bills = $this->latestBills(VashiMarketBill::query(), 5);
        if ($bills->isEmpty()) {
            return 'There are no Vashi Market bills yet.';
        }

        $lines = ['Latest Vashi Market bills', ''];
        foreach ($bills as $bill) {
            $lines[] = $this->billLine($bill);
        }

        return implode("\n", $lines);
    }

    private function billByNumber(string $number): string
    {
        $exact = VashiMarketBill::query()->where('bill_no', $number)->first();
        if ($exact) {
            return $this->billDetail($exact);
        }

        $bills = VashiMarketBill::query()
            ->where('bill_no', 'like', $this->likeTerm($number))
            ->orderByDesc('id')
            ->limit(6)
            ->get(['id', 'bill_date', 'bill_no', 'party_name', 'total_bill_amount', 'is_paid']);

        if ($bills->isEmpty()) {
            return 'I could not find bill '.$number.'.';
        }

        if ($bills->count() === 1) {
            $bill = VashiMarketBill::query()->find($bills->first()->id);

            return $bill ? $this->billDetail($bill) : 'I could not find bill '.$number.'.';
        }

        $lines = ['Bills matching '.$number, ''];
        foreach ($bills as $bill) {
            $lines[] = $this->billLine($bill);
        }

        return implode("\n", $lines);
    }

    private function billDetail(VashiMarketBill $bill): string
    {
        $bill->loadMissing([
            'products:id,vashi_market_bill_id,product_name,brand_name,total_kg,product_amount',
        ]);

        $lines = [
            'Bill '.$bill->bill_no,
            '',
            'Date: '.$this->formatDate($bill->bill_date),
            'Party: '.$bill->party_name,
            'Dalal: '.($bill->dalal ?: '—'),
            'Transport: '.($bill->transport_name ?: '—'),
            'Amount: '.$this->money($bill->total_bill_amount),
            'Status: '.($bill->is_paid ? 'Paid' : 'Unpaid'),
        ];

        if ($bill->is_paid) {
            $lines[] = 'Paid amount: '.$this->money($bill->paid_amount);
            if ($bill->paid_date) {
                $lines[] = 'Paid on: '.$this->formatDate($bill->paid_date);
            }
            if ($bill->payment_difference !== null && (float) $bill->payment_difference != 0.0) {
                $lines[] = 'Difference: '.$this->money($bill->payment_difference);
            }
        }

        if ($bill->products->isNotEmpty()) {
            $lines[] = '';
            $lines[] = 'Products:';
            foreach ($bill->products->take(8) as $product) {
                $name = $product->product_name;
                if ($product->brand_name) {
                    $name .= ' ('.$product->brand_name.')';
                }
                $bits = [$name];
                if ($this->hasPrice($product->total_kg)) {
                    $bits[] = rtrim(rtrim(number_format((float) $product->total_kg, 2), '0'), '.').' kg';
                }
                if ($product->product_amount !== null && $product->product_amount !== '') {
                    $bits[] = $this->money($product->product_amount);
                }
                $lines[] = '• '.implode(' · ', $bits);
            }

            $extra = $bill->products->count() - 8;
            if ($extra > 0) {
                $lines[] = '• '.$extra.' more';
            }
        }

        return implode("\n", $lines);
    }

    private function billsForParty(string $party, bool $unpaidOnly): string
    {
        if (mb_strlen($party) < 2) {
            return 'Enter a party name with at least 2 characters.';
        }

        $query = VashiMarketBill::query()->where('party_name', 'like', $this->likeTerm($party));
        if ($unpaidOnly) {
            $query->where('is_paid', false);
        }

        $count = (clone $query)->count();
        $scope = $unpaidOnly ? 'unpaid bills' : 'bills';

        if ($count === 0) {
            return 'I could not find '.$scope.' for "'.Str::limit($party, 80, '').'".';
        }

        $amount = (clone $query)->sum('total_bill_amount');
        $bills = $this->latestBills($query, 8);
        $lines = [
            'Found '.$count.' '.$scope.' matching "'.Str::limit($party, 80, '').'".',
            'Total: '.$this->money($amount),
        ];

        if ($count > $bills->count()) {
            $lines[] = 'Showing the latest '.$bills->count().'.';
        }

        $lines[] = '';
        foreach ($bills as $bill) {
            $lines[] = $this->billLine($bill);
        }

        return implode("\n", $lines);
    }

    private function latestBills($query, int $limit)
    {
        return (clone $query)
            ->with(['products:id,vashi_market_bill_id,product_name'])
            ->orderByDesc('bill_date')
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'bill_date', 'bill_no', 'party_name', 'total_bill_amount', 'is_paid']);
    }

    private function billLine(VashiMarketBill $bill): string
    {
        $parts = [
            $this->formatDate($bill->bill_date),
            'Bill '.$bill->bill_no,
            $bill->party_name,
        ];

        if ($bill->relationLoaded('products')) {
            $products = $bill->products->pluck('product_name')->filter()->unique()->take(3)->implode(', ');
            if ($products !== '') {
                $parts[] = $products;
            }
        }

        $parts[] = $this->money($bill->total_bill_amount);
        $parts[] = $bill->is_paid ? 'Paid' : 'Unpaid';

        return '• '.implode(' · ', $parts);
    }

    private function searchWords(string $query): array
    {
        $cleaned = Str::lower($query);
        $cleaned = preg_replace('/\b(the|a|an|of|for|please|product|products|price|prices|rate|rates|cost|show|me)\b/', ' ', $cleaned) ?? $cleaned;
        $parts = preg_split('/\s+/', trim($cleaned)) ?: [];
        $words = [];

        foreach ($parts as $part) {
            $part = trim($part, '?.!,');
            if (mb_strlen($part) >= 2) {
                $words[] = $part;
            }
        }

        return array_values(array_unique($words));
    }

    private function likeTerm(string $value): string
    {
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);

        return '%'.$escaped.'%';
    }

    private function hasPrice($value): bool
    {
        return $value !== null && $value !== '';
    }

    private function money($amount): string
    {
        return '₹'.number_format((float) $amount, 2);
    }

    private function formatDate($value): string
    {
        if (! $value) {
            return '—';
        }

        return Carbon::parse($value)->format('d/m/Y');
    }

    private function typeLabel(?string $type): string
    {
        return match ($type) {
            'grocery' => 'Grocery',
            'oil' => 'Oil',
            'masala' => 'Masala',
            'other' => 'Other',
            default => 'Uncategorised',
        };
    }
}
