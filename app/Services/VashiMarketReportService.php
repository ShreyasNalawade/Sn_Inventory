<?php

namespace App\Services;

use App\Models\VashiMarketBill;
use App\Models\VashiMarketBillProduct;
use Illuminate\Support\Collection;

class VashiMarketReportService
{
    /**
     * Paid extra is the stored difference. A stored zero is ignored when the
     * paid amount and bill total do not match, so older bills still report
     * the real interest or offer.
     */
    private const INTEREST_SQL = <<<'SQL'
CASE
    WHEN payment_difference IS NOT NULL AND payment_difference <> 0 THEN payment_difference
    WHEN paid_amount IS NOT NULL
        AND total_bill_amount IS NOT NULL
        AND ABS(paid_amount - total_bill_amount) > 0.009
        THEN paid_amount - total_bill_amount
    ELSE COALESCE(payment_difference, 0)
END
SQL;

    /**
     * @param  array{bill: ?array{0: string, 1: string}, received: ?array{0: string, 1: string}, paid: ?array{0: string, 1: string}}  $ranges
     * @return array{
     *     summary: array<string, float|int>,
     *     products: Collection<int, object>,
     *     parties: Collection<int, object>,
     *     dalals: Collection<int, object>,
     *     interestBills: Collection<int, object>
     * }
     */
    public function build(array $ranges): array
    {
        $summary = $this->summary($ranges);
        $products = $this->products($ranges);
        $parties = $this->parties($ranges);
        $interest = $this->interest($ranges);

        $maxKg = (float) ($products->max('kg') ?: 0);
        $products = $products->map(function (object $row) use ($maxKg) {
            $row->kg_share = $maxKg > 0 ? round(((float) $row->kg / $maxKg) * 100, 1) : 0;

            return $row;
        });

        $maxParty = (float) ($parties->max('bill_amount') ?: 0);
        $parties = $parties->map(function (object $row) use ($maxParty) {
            $row->amount_share = $maxParty > 0 ? round(((float) $row->bill_amount / $maxParty) * 100, 1) : 0;

            return $row;
        });

        $moneyMax = max(
            (float) $summary['bill_amount'],
            (float) $summary['paid_amount'],
            (float) $summary['remaining_amount'],
            1
        );

        return [
            'summary' => array_merge($summary, [
                'bags_received' => (int) $products->sum('bags'),
                'kg_received' => round((float) $products->sum('kg'), 2),
                'interest_paid' => $interest['interest_paid'],
                'offer_received' => $interest['offer_received'],
                'bill_share' => round(((float) $summary['bill_amount'] / $moneyMax) * 100, 1),
                'paid_share' => round(((float) $summary['paid_amount'] / $moneyMax) * 100, 1),
                'remaining_share' => round(((float) $summary['remaining_amount'] / $moneyMax) * 100, 1),
            ]),
            'products' => $products,
            'parties' => $parties,
            'dalals' => $this->pendingByDalal($ranges),
            'interestBills' => $interest['bills'],
        ];
    }

    /**
     * @param  array{bill: ?array{0: string, 1: string}, received: ?array{0: string, 1: string}, paid: ?array{0: string, 1: string}}  $ranges
     * @return array{bill_count: int, paid_count: int, unpaid_count: int, bill_amount: float, paid_amount: float, remaining_amount: float}
     */
    private function summary(array $ranges): array
    {
        $query = VashiMarketBill::query();
        $this->constrain($query, $ranges);

        $row = $query
            ->selectRaw('COUNT(*) as bill_count')
            ->selectRaw('SUM(CASE WHEN is_paid = 1 THEN 1 ELSE 0 END) as paid_count')
            ->selectRaw('SUM(CASE WHEN is_paid = 0 THEN 1 ELSE 0 END) as unpaid_count')
            ->selectRaw('COALESCE(SUM(total_bill_amount), 0) as bill_amount')
            ->selectRaw('COALESCE(SUM(CASE WHEN is_paid = 1 THEN paid_amount ELSE 0 END), 0) as paid_amount')
            ->selectRaw('COALESCE(SUM(CASE WHEN is_paid = 0 THEN total_bill_amount ELSE 0 END), 0) as remaining_amount')
            ->first();

        return [
            'bill_count' => (int) ($row->bill_count ?? 0),
            'paid_count' => (int) ($row->paid_count ?? 0),
            'unpaid_count' => (int) ($row->unpaid_count ?? 0),
            'bill_amount' => round((float) ($row->bill_amount ?? 0), 2),
            'paid_amount' => round((float) ($row->paid_amount ?? 0), 2),
            'remaining_amount' => round((float) ($row->remaining_amount ?? 0), 2),
        ];
    }

    /**
     * @param  array{bill: ?array{0: string, 1: string}, received: ?array{0: string, 1: string}, paid: ?array{0: string, 1: string}}  $ranges
     * @return Collection<int, object>
     */
    private function products(array $ranges): Collection
    {
        $query = VashiMarketBillProduct::query()
            ->join('vashi_market_bills as bills', 'bills.id', '=', 'vashi_market_bill_products.vashi_market_bill_id');
        $this->constrain($query, $ranges, 'bills.');

        return $query
            ->groupBy('vashi_market_bill_products.product_name')
            ->selectRaw('vashi_market_bill_products.product_name as product_name')
            ->selectRaw('COUNT(DISTINCT bills.id) as bill_count')
            ->selectRaw('COALESCE(SUM(vashi_market_bill_products.num_bags), 0) as bags')
            ->selectRaw('COALESCE(SUM(vashi_market_bill_products.total_kg), 0) as kg')
            ->selectRaw('COALESCE(SUM(vashi_market_bill_products.product_amount), 0) as amount')
            ->orderByDesc('kg')
            ->get()
            ->map(function (object $row) {
                $row->bill_count = (int) $row->bill_count;
                $row->bags = (int) $row->bags;
                $row->kg = round((float) $row->kg, 2);
                $row->amount = round((float) $row->amount, 2);

                return $row;
            });
    }

    /**
     * @param  array{bill: ?array{0: string, 1: string}, received: ?array{0: string, 1: string}, paid: ?array{0: string, 1: string}}  $ranges
     * @return Collection<int, object>
     */
    private function parties(array $ranges): Collection
    {
        $sql = self::INTEREST_SQL;
        $query = VashiMarketBill::query();
        $this->constrain($query, $ranges);

        return $query
            ->groupBy('party_name')
            ->select('party_name')
            ->selectRaw('COUNT(*) as bill_count')
            ->selectRaw('SUM(CASE WHEN is_paid = 1 THEN 1 ELSE 0 END) as paid_count')
            ->selectRaw('SUM(CASE WHEN is_paid = 0 THEN 1 ELSE 0 END) as unpaid_count')
            ->selectRaw('COALESCE(SUM(total_bill_amount), 0) as bill_amount')
            ->selectRaw('COALESCE(SUM(CASE WHEN is_paid = 1 THEN paid_amount ELSE 0 END), 0) as paid_amount')
            ->selectRaw('COALESCE(SUM(CASE WHEN is_paid = 0 THEN total_bill_amount ELSE 0 END), 0) as remaining_amount')
            ->selectRaw("COALESCE(SUM(CASE WHEN is_paid = 1 AND ($sql) > 0 THEN ($sql) ELSE 0 END), 0) as interest")
            ->orderByDesc('bill_amount')
            ->get()
            ->map(function (object $row) {
                $row->party_name = $row->party_name !== null && $row->party_name !== '' ? $row->party_name : '—';
                $row->bill_count = (int) $row->bill_count;
                $row->paid_count = (int) $row->paid_count;
                $row->unpaid_count = (int) $row->unpaid_count;
                $row->bill_amount = round((float) $row->bill_amount, 2);
                $row->paid_amount = round((float) $row->paid_amount, 2);
                $row->remaining_amount = round((float) $row->remaining_amount, 2);
                $row->interest = round((float) $row->interest, 2);

                return $row;
            });
    }

    /**
     * @param  array{bill: ?array{0: string, 1: string}, received: ?array{0: string, 1: string}, paid: ?array{0: string, 1: string}}  $ranges
     * @return Collection<int, object>
     */
    private function pendingByDalal(array $ranges): Collection
    {
        $query = VashiMarketBill::query()->where('is_paid', false);
        $this->constrain($query, $ranges);

        return $query
            ->groupBy('dalal')
            ->select('dalal')
            ->selectRaw('COUNT(*) as bill_count')
            ->selectRaw('COALESCE(SUM(total_bill_amount), 0) as amount')
            ->orderByDesc('amount')
            ->get()
            ->map(function (object $row) {
                $row->dalal = $row->dalal !== null && $row->dalal !== '' ? $row->dalal : '—';
                $row->bill_count = (int) $row->bill_count;
                $row->amount = round((float) $row->amount, 2);

                return $row;
            });
    }

    /**
     * @param  array{bill: ?array{0: string, 1: string}, received: ?array{0: string, 1: string}, paid: ?array{0: string, 1: string}}  $ranges
     * @return array{interest_paid: float, offer_received: float, bills: Collection<int, object>}
     */
    private function interest(array $ranges): array
    {
        $sql = self::INTEREST_SQL;

        $totalsQuery = VashiMarketBill::query()->where('is_paid', true);
        $this->constrain($totalsQuery, $ranges);
        $totals = $totalsQuery
            ->selectRaw("COALESCE(SUM(CASE WHEN ($sql) > 0 THEN ($sql) ELSE 0 END), 0) as interest_paid")
            ->selectRaw("COALESCE(SUM(CASE WHEN ($sql) < 0 THEN ABS($sql) ELSE 0 END), 0) as offer_received")
            ->first();

        $billsQuery = VashiMarketBill::query()
            ->where('is_paid', true)
            ->whereRaw("($sql) > 0");
        $this->constrain($billsQuery, $ranges);
        $bills = $billsQuery
            ->select([
                'id',
                'bill_no',
                'party_name',
                'dalal',
                'total_bill_amount',
                'paid_amount',
            ])
            ->selectRaw("($sql) as interest")
            ->orderByDesc('interest')
            ->get()
            ->map(function (object $row) {
                $total = (float) $row->total_bill_amount;
                $row->total_bill_amount = round($total, 2);
                $row->paid_amount = round((float) $row->paid_amount, 2);
                $row->interest = round((float) $row->interest, 2);
                $row->interest_percent = $total > 0
                    ? round((abs($row->interest) / $total) * 100, 2)
                    : 0;
                $row->dalal = $row->dalal !== null && $row->dalal !== '' ? $row->dalal : '—';

                return $row;
            });

        return [
            'interest_paid' => round((float) ($totals->interest_paid ?? 0), 2),
            'offer_received' => round((float) ($totals->offer_received ?? 0), 2),
            'bills' => $bills,
        ];
    }

    /**
     * @param  array{bill: ?array{0: string, 1: string}, received: ?array{0: string, 1: string}, paid: ?array{0: string, 1: string}}  $ranges
     */
    private function constrain(object $query, array $ranges, string $prefix = ''): void
    {
        $columns = [
            'bill' => 'bill_date',
            'received' => 'received_date',
            'paid' => 'paid_date',
        ];

        foreach ($columns as $key => $column) {
            if (! empty($ranges[$key])) {
                $query->whereBetween($prefix.$column, $ranges[$key]);
            }
        }
    }
}
