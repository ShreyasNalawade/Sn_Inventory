@extends('layouts.app')

@section('styles')
    <style>
        .report-note {
            margin: 0 0 1rem;
            color: #64748b;
            font-size: 0.85rem;
        }

        .filter-block {
            margin-bottom: 0.85rem;
            padding: 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.8rem;
            background: #f8fafc;
        }

        .filter-block-title {
            margin: 0 0 0.55rem;
            color: #0f172a;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .report-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
        }

        .money-bars {
            display: grid;
            gap: 0.55rem;
            margin-bottom: 1rem;
        }

        .money-bar-row {
            display: grid;
            grid-template-columns: 5.5rem minmax(0, 1fr);
            gap: 0.55rem;
            align-items: center;
        }

        .money-bar-label {
            color: #475569;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .money-bar-track {
            height: 1.35rem;
            overflow: hidden;
            border-radius: 999px;
            background: #f1f5f9;
        }

        .money-bar-track span {
            display: flex;
            align-items: center;
            min-width: 4.5rem;
            height: 100%;
            padding: 0 0.55rem;
            border-radius: inherit;
            color: #fff;
            font-size: 0.72rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .money-bar-track .bill-fill {
            background: #334155;
        }

        .money-bar-track .paid-fill {
            background: #15803d;
        }

        .money-bar-track .due-fill {
            background: #ea580c;
        }

        .report-summary {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.65rem;
            margin-bottom: 1.25rem;
        }

        .report-tile {
            min-width: 0;
            padding: 0.75rem 0.8rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.8rem;
            background: #fff;
        }

        .report-tile span {
            display: block;
            color: #64748b;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .report-tile strong {
            display: block;
            margin-top: 0.2rem;
            color: #0f172a;
            font-size: 1.05rem;
            line-height: 1.25;
            word-break: break-word;
        }

        .report-tile.alert-tile strong {
            color: #c2410c;
        }

        .report-section + .report-section {
            margin-top: 1.35rem;
            padding-top: 1.15rem;
            border-top: 1px solid #eef2f7;
        }

        .report-section h4 {
            margin: 0 0 0.35rem;
            font-size: 1rem;
            font-weight: 700;
        }

        .report-section p {
            margin: 0 0 0.8rem;
            color: #64748b;
            font-size: 0.82rem;
        }

        .kg-bars {
            display: grid;
            gap: 0.45rem;
            margin-bottom: 0.9rem;
        }

        .kg-bar-row {
            display: grid;
            grid-template-columns: minmax(0, 7.5rem) minmax(0, 1fr) auto;
            gap: 0.5rem;
            align-items: center;
        }

        .kg-bar-name,
        .kg-bar-value {
            min-width: 0;
            color: #0f172a;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .kg-bar-name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .kg-bar-track {
            height: 0.45rem;
            overflow: hidden;
            border-radius: 999px;
            background: #f1f5f9;
        }

        .kg-bar-track span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: #ff6347;
        }

        .report-table {
            width: 100%;
            margin: 0;
        }

        .report-table th,
        .report-table td {
            padding: 0.55rem 0.45rem;
            vertical-align: middle;
        }

        .report-table th button {
            padding: 0;
            border: 0;
            background: transparent;
            color: inherit;
            font-weight: 700;
            text-align: left;
        }

        .report-empty {
            margin: 0;
            color: #64748b;
        }

        @media (min-width: 768px) {
            .report-summary {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .report-filters {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 0.75rem;
            }

            .filter-block {
                margin-bottom: 0;
            }

            .report-actions {
                grid-template-columns: 10rem 10rem;
                justify-content: start;
            }
        }

        @media (min-width: 1200px) {
            .report-summary {
                grid-template-columns: repeat(6, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .report-table thead {
                display: none;
            }

            .report-table,
            .report-table tbody,
            .report-table tr,
            .report-table td {
                display: block;
                width: 100%;
            }

            .report-table tr {
                margin-bottom: 0.7rem;
                padding: 0.35rem 0.7rem;
                border: 1px solid #e5e7eb;
                border-radius: 0.8rem;
                background: #fff;
            }

            .report-table td {
                display: flex;
                justify-content: space-between;
                gap: 0.75rem;
                padding: 0.45rem 0;
                border-bottom: 1px solid #f1f5f9;
                text-align: right;
            }

            .report-table td:last-child {
                border-bottom: 0;
            }

            .report-table td::before {
                content: attr(data-label);
                color: #64748b;
                font-size: 0.75rem;
                font-weight: 700;
                text-align: left;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $summary = $report['summary'];
    @endphp

    <div class="page-content container-fluid">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h3 class="card-title text-center fw-bold text-dark mb-0">Daily Report</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.daily-report') }}" method="GET" class="mb-3">
                    <div class="report-filters">
                        @foreach ([
                            'bill' => 'Bill date',
                            'received' => 'Received date',
                            'paid' => 'Paid date',
                        ] as $key => $label)
                            <div class="filter-block">
                                <p class="filter-block-title">{{ $label }}</p>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label for="{{ $key }}_start" class="form-label">From</label>
                                        @include('admin.partials.ddmmyyyy-date', [
                                            'id' => $key.'_start',
                                            'name' => $key.'_start',
                                            'value' => $ranges[$key][0] ?? '',
                                        ])
                                    </div>
                                    <div class="col-6">
                                        <label for="{{ $key }}_end" class="form-label">To</label>
                                        @include('admin.partials.ddmmyyyy-date', [
                                            'id' => $key.'_end',
                                            'name' => $key.'_end',
                                            'value' => $ranges[$key][1] ?? '',
                                        ])
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="report-actions">
                        <button type="submit" class="btn btn-info">Show</button>
                        <a href="{{ route('admin.daily-report') }}" class="btn btn-outline-secondary">This month</a>
                    </div>
                </form>

                <p class="report-note">
                    Leave a date pair empty to ignore it. Fill only the dates you want.
                    Bill date, received date, and paid date can be used together.
                </p>

                <div class="money-bars">
                    <div class="money-bar-row">
                        <span class="money-bar-label">Bill amount</span>
                        <span class="money-bar-track"><span class="bill-fill" style="width: {{ max($summary['bill_share'], $summary['bill_amount'] > 0 ? 18 : 0) }}%">₹{{ number_format($summary['bill_amount'], 2) }}</span></span>
                    </div>
                    <div class="money-bar-row">
                        <span class="money-bar-label">Paid</span>
                        <span class="money-bar-track"><span class="paid-fill" style="width: {{ max($summary['paid_share'], $summary['paid_amount'] > 0 ? 18 : 0) }}%">₹{{ number_format($summary['paid_amount'], 2) }}</span></span>
                    </div>
                    <div class="money-bar-row">
                        <span class="money-bar-label">Remaining</span>
                        <span class="money-bar-track"><span class="due-fill" style="width: {{ max($summary['remaining_share'], $summary['remaining_amount'] > 0 ? 18 : 0) }}%">₹{{ number_format($summary['remaining_amount'], 2) }}</span></span>
                    </div>
                </div>

                <div class="report-summary">
                    <div class="report-tile">
                        <span>Bills</span>
                        <strong>{{ number_format($summary['bill_count']) }}</strong>
                    </div>
                    <div class="report-tile">
                        <span>Paid bills</span>
                        <strong>{{ number_format($summary['paid_count']) }}</strong>
                    </div>
                    <div class="report-tile alert-tile">
                        <span>Unpaid bills</span>
                        <strong>{{ number_format($summary['unpaid_count']) }}</strong>
                    </div>
                    <div class="report-tile">
                        <span>Bill amount</span>
                        <strong>₹{{ number_format($summary['bill_amount'], 2) }}</strong>
                    </div>
                    <div class="report-tile">
                        <span>Paid amount</span>
                        <strong>₹{{ number_format($summary['paid_amount'], 2) }}</strong>
                    </div>
                    <div class="report-tile alert-tile">
                        <span>Remaining</span>
                        <strong>₹{{ number_format($summary['remaining_amount'], 2) }}</strong>
                    </div>
                    <div class="report-tile">
                        <span>Interest paid</span>
                        <strong>₹{{ number_format($summary['interest_paid'], 2) }}</strong>
                    </div>
                    <div class="report-tile">
                        <span>Offer received</span>
                        <strong>₹{{ number_format($summary['offer_received'], 2) }}</strong>
                    </div>
                    <div class="report-tile">
                        <span>Bags</span>
                        <strong>{{ number_format($summary['bags_received']) }}</strong>
                    </div>
                    <div class="report-tile">
                        <span>Kg received</span>
                        <strong>{{ number_format($summary['kg_received'], 2) }}</strong>
                    </div>
                </div>

                <section class="report-section">
                    <h4>Goods received</h4>
                    <p>Bags, kg, and amount for each product in the selected dates.</p>
                    @if ($report['products']->isEmpty())
                        <p class="report-empty">No products were received in this range.</p>
                    @else
                        <div class="kg-bars">
                            @foreach ($report['products']->take(8) as $product)
                                <div class="kg-bar-row">
                                    <span class="kg-bar-name">{{ $product->product_name }}</span>
                                    <span class="kg-bar-track"><span style="width: {{ $product->kg_share }}%"></span></span>
                                    <span class="kg-bar-value">{{ number_format($product->kg, 2) }} kg</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="table-responsive">
                            <table class="table report-table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th><button type="button" data-sort="text">Product</button></th>
                                        <th><button type="button" data-sort="number">Bills</button></th>
                                        <th><button type="button" data-sort="number">Bags</button></th>
                                        <th><button type="button" data-sort="number">Kg</button></th>
                                        <th><button type="button" data-sort="number">Amount</button></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($report['products'] as $product)
                                        <tr>
                                            <td data-label="Product" data-value="{{ $product->product_name }}">{{ $product->product_name }}</td>
                                            <td data-label="Bills" data-value="{{ $product->bill_count }}">{{ number_format($product->bill_count) }}</td>
                                            <td data-label="Bags" data-value="{{ $product->bags }}">{{ number_format($product->bags) }}</td>
                                            <td data-label="Kg" data-value="{{ $product->kg }}">{{ number_format($product->kg, 2) }}</td>
                                            <td data-label="Amount" data-value="{{ $product->amount }}">₹{{ number_format($product->amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>

                <section class="report-section">
                    <h4>By party</h4>
                    <p>Bill amount, amount paid, amount still remaining, and interest for each party.</p>
                    @if ($report['parties']->isEmpty())
                        <p class="report-empty">No parties in this selection.</p>
                    @else
                        <div class="kg-bars">
                            @foreach ($report['parties']->take(6) as $party)
                                <div class="kg-bar-row">
                                    <span class="kg-bar-name">{{ $party->party_name }}</span>
                                    <span class="kg-bar-track"><span style="width: {{ $party->amount_share }}%"></span></span>
                                    <span class="kg-bar-value">₹{{ number_format($party->bill_amount, 0) }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="table-responsive">
                            <table class="table report-table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th><button type="button" data-sort="text">Party</button></th>
                                        <th><button type="button" data-sort="number">Bills</button></th>
                                        <th><button type="button" data-sort="number">Paid bills</button></th>
                                        <th><button type="button" data-sort="number">Unpaid</button></th>
                                        <th><button type="button" data-sort="number">Bill amount</button></th>
                                        <th><button type="button" data-sort="number">Paid</button></th>
                                        <th><button type="button" data-sort="number">Remaining</button></th>
                                        <th><button type="button" data-sort="number">Interest</button></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($report['parties'] as $party)
                                        <tr>
                                            <td data-label="Party" data-value="{{ $party->party_name }}">{{ $party->party_name }}</td>
                                            <td data-label="Bills" data-value="{{ $party->bill_count }}">{{ number_format($party->bill_count) }}</td>
                                            <td data-label="Paid bills" data-value="{{ $party->paid_count }}">{{ number_format($party->paid_count) }}</td>
                                            <td data-label="Unpaid" data-value="{{ $party->unpaid_count }}">{{ number_format($party->unpaid_count) }}</td>
                                            <td data-label="Bill amount" data-value="{{ $party->bill_amount }}">₹{{ number_format($party->bill_amount, 2) }}</td>
                                            <td data-label="Paid" data-value="{{ $party->paid_amount }}">₹{{ number_format($party->paid_amount, 2) }}</td>
                                            <td data-label="Remaining" data-value="{{ $party->remaining_amount }}">₹{{ number_format($party->remaining_amount, 2) }}</td>
                                            <td data-label="Interest" data-value="{{ $party->interest }}">₹{{ number_format($party->interest, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>

                <section class="report-section">
                    <h4>Pending by dalal</h4>
                    <p>Unpaid bills in this selection, grouped by dalal.</p>
                    @if ($report['dalals']->isEmpty())
                        <p class="report-empty">No unpaid bills in this range.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table report-table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th><button type="button" data-sort="text">Dalal</button></th>
                                        <th><button type="button" data-sort="number">Unpaid bills</button></th>
                                        <th><button type="button" data-sort="number">Pending amount</button></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($report['dalals'] as $dalal)
                                        <tr>
                                            <td data-label="Dalal" data-value="{{ $dalal->dalal }}">{{ $dalal->dalal }}</td>
                                            <td data-label="Unpaid bills" data-value="{{ $dalal->bill_count }}">{{ number_format($dalal->bill_count) }}</td>
                                            <td data-label="Pending amount" data-value="{{ $dalal->amount }}">₹{{ number_format($dalal->amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>

                <section class="report-section">
                    <h4>Interest paid</h4>
                    <p>Bills in this selection where the payment was higher than the bill amount.</p>
                    @if ($report['interestBills']->isEmpty())
                        <p class="report-empty">No interest was paid in this range.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table report-table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th><button type="button" data-sort="text">Bill</button></th>
                                        <th><button type="button" data-sort="text">Party</button></th>
                                        <th><button type="button" data-sort="text">Dalal</button></th>
                                        <th><button type="button" data-sort="number">Bill amount</button></th>
                                        <th><button type="button" data-sort="number">Paid</button></th>
                                        <th><button type="button" data-sort="number">Interest</button></th>
                                        <th><button type="button" data-sort="number">Percent</button></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($report['interestBills'] as $bill)
                                        <tr>
                                            <td data-label="Bill" data-value="{{ $bill->bill_no }}">
                                                <a href="{{ route('vashi-market.showBillDetails', $bill->id) }}">{{ $bill->bill_no }}</a>
                                            </td>
                                            <td data-label="Party" data-value="{{ $bill->party_name }}">{{ $bill->party_name }}</td>
                                            <td data-label="Dalal" data-value="{{ $bill->dalal }}">{{ $bill->dalal }}</td>
                                            <td data-label="Bill amount" data-value="{{ $bill->total_bill_amount }}">₹{{ number_format($bill->total_bill_amount, 2) }}</td>
                                            <td data-label="Paid" data-value="{{ $bill->paid_amount }}">₹{{ number_format($bill->paid_amount, 2) }}</td>
                                            <td data-label="Interest" data-value="{{ $bill->interest }}">₹{{ number_format($bill->interest, 2) }}</td>
                                            <td data-label="Percent" data-value="{{ $bill->interest_percent }}">{{ number_format($bill->interest_percent, 2) }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.report-table').forEach((table) => {
                const headers = Array.from(table.querySelectorAll('thead th button'));

                headers.forEach((button, index) => {
                    button.addEventListener('click', function () {
                        const tbody = table.querySelector('tbody');
                        const rows = Array.from(tbody.querySelectorAll('tr'));
                        const nextDirection = button.dataset.direction === 'asc' ? 'desc' : 'asc';

                        headers.forEach((header) => {
                            delete header.dataset.direction;
                        });
                        button.dataset.direction = nextDirection;

                        rows.sort((left, right) => {
                            const leftValue = left.children[index].dataset.value || '';
                            const rightValue = right.children[index].dataset.value || '';
                            const result = button.dataset.sort === 'number'
                                ? Number(leftValue) - Number(rightValue)
                                : leftValue.localeCompare(rightValue, undefined, { numeric: true, sensitivity: 'base' });

                            return nextDirection === 'asc' ? result : -result;
                        });

                        rows.forEach((row) => tbody.appendChild(row));
                    });
                });
            });
        });
    </script>
@endsection
