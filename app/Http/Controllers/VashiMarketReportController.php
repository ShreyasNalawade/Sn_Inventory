<?php

namespace App\Http\Controllers;

use App\Services\VashiMarketReportService;
use Illuminate\Http\Request;

class VashiMarketReportController extends Controller
{
    public function __construct(private VashiMarketReportService $reports)
    {
    }

    public function index(Request $request)
    {
        $keys = [
            'bill_start', 'bill_end',
            'received_start', 'received_end',
            'paid_start', 'paid_end',
        ];
        $firstVisit = ! $request->hasAny($keys);

        $ranges = [
            'bill' => $this->range(
                $request->input('bill_start'),
                $request->input('bill_end'),
                $firstVisit
            ),
            'received' => $this->range($request->input('received_start'), $request->input('received_end')),
            'paid' => $this->range($request->input('paid_start'), $request->input('paid_end')),
        ];

        return view('admin.vashiMarketReport', [
            'report' => $this->reports->build($ranges),
            'ranges' => $ranges,
        ]);
    }

    /**
     * @return array{0: string, 1: string}|null
     */
    private function range(mixed $start, mixed $end, bool $defaultToThisMonth = false): ?array
    {
        $startDate = $this->validDate($start);
        $endDate = $this->validDate($end);

        if ($startDate === null && $endDate === null) {
            if (! $defaultToThisMonth) {
                return null;
            }

            return [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()];
        }

        $startDate ??= $endDate;
        $endDate ??= $startDate;

        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        return [$startDate, $endDate];
    }

    private function validDate(mixed $value): ?string
    {
        $value = trim((string) $value);

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        [$year, $month, $day] = array_map('intval', explode('-', $value));

        return checkdate($month, $day, $year) ? $value : null;
    }
}
