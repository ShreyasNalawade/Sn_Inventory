@extends('layouts.app')

@section('content')
    @php
        $difference = (float) ($bill->payment_difference ?? 0);
        $totalBillAmount = (float) $bill->total_bill_amount;
        $differencePercent = $totalBillAmount > 0 ? (abs($difference) / $totalBillAmount) * 100 : 0;
    @endphp

    <style>
        @media (min-width: 992px) {
            .bill-detail-desktop .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .bill-detail-desktop .table-responsive table {
                min-width: 800px;
            }
        }

        @media (max-width: 991.98px) {
            .bill-detail-header {
                align-items: stretch !important;
                gap: 0.75rem;
            }

            .bill-detail-header .card-title {
                margin-bottom: 0 !important;
                font-size: 1.15rem;
                line-height: 1.3;
            }

            .bill-detail-actions {
                display: grid;
                grid-template-columns: 1fr 1.4fr;
                gap: 0.5rem;
                width: 100%;
            }

            .bill-detail-actions .btn {
                width: 100%;
                margin: 0 !important;
                white-space: nowrap;
            }

            .bill-section + .bill-section {
                margin-top: 1.15rem;
                padding-top: 1.15rem;
                border-top: 1px solid #eef2f7;
            }

            .section-title {
                margin: 0 0 0.7rem;
                font-size: 0.95rem;
                font-weight: 700;
                color: #111827;
            }

            .section-heading {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                margin-bottom: 0.7rem;
            }

            .section-heading .section-title {
                margin-bottom: 0;
            }

            .status-pill {
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
                padding: 0.28rem 0.65rem;
                border-radius: 999px;
                font-size: 0.75rem;
                font-weight: 700;
                white-space: nowrap;
            }

            .status-pill.paid {
                background: #dcfce7;
                color: #166534;
            }

            .status-pill.unpaid {
                background: #fef3c7;
                color: #92400e;
            }

            .detail-list {
                display: grid;
                gap: 0;
            }

            .detail-item {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 0.85rem;
                padding: 0.62rem 0;
                border-bottom: 1px solid #f1f5f9;
            }

            .detail-item:last-child {
                border-bottom: 0;
            }

            .detail-label {
                flex: 0 0 auto;
                max-width: 46%;
                color: #64748b;
                font-size: 0.8rem;
                font-weight: 600;
                line-height: 1.35;
            }

            .detail-value {
                min-width: 0;
                color: #0f172a;
                font-size: 0.92rem;
                font-weight: 700;
                line-height: 1.35;
                text-align: right;
                word-break: break-word;
            }

            .total-banner {
                display: flex;
                justify-content: flex-end;
                margin-top: 0.85rem;
            }

            .total-banner-chip {
                display: inline-flex;
                align-items: baseline;
                justify-content: flex-end;
                flex-wrap: wrap;
                gap: 0.4rem 0.55rem;
                max-width: 100%;
                padding: 0.45rem 0.75rem;
                border-radius: 999px;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
            }

            .total-banner span {
                color: #64748b;
                font-size: 0.78rem;
                font-weight: 600;
            }

            .total-banner strong {
                color: #0f172a;
                font-size: 1.05rem;
                font-weight: 800;
                line-height: 1.2;
            }

            .interest-note {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                margin-top: 0.75rem;
                padding: 0.7rem 0.85rem;
                border-radius: 0.8rem;
                font-size: 0.86rem;
                font-weight: 700;
            }

            .interest-note.interest {
                background: #fef2f2;
                border: 1px solid #fecaca;
                color: #b91c1c;
            }

            .interest-note.offer {
                background: #f0fdf4;
                border: 1px solid #bbf7d0;
                color: #166534;
            }

            .interest-note.even {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                color: #475569;
            }

            .unpaid-note {
                margin: 0;
                padding: 0.75rem 0.85rem;
                border-radius: 0.8rem;
                background: #fffbeb;
                border: 1px solid #fde68a;
                color: #92400e;
                font-size: 0.88rem;
                font-weight: 600;
            }

            .product-list,
            .group-list {
                display: grid;
                gap: 0.7rem;
            }

            .product-card,
            .group-card {
                padding: 0.8rem 0.85rem;
                border: 1px solid #e5e7eb;
                border-radius: 0.85rem;
                background: #fff;
            }

            .group-card.is-current {
                border-color: #86efac;
                background: #f0fdf4;
            }

            .product-card-top,
            .group-card-top {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 0.75rem;
            }

            .product-card-name,
            .group-card-name {
                display: block;
                color: #111827;
                font-size: 0.98rem;
                font-weight: 700;
                line-height: 1.3;
                word-break: break-word;
            }

            .product-card-brand {
                display: block;
                margin-top: 0.15rem;
                color: #64748b;
                font-size: 0.8rem;
            }

            .product-card-amount {
                flex-shrink: 0;
                color: #c2410c;
                font-size: 0.95rem;
                font-weight: 700;
                white-space: nowrap;
            }

            .product-stats {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 0.4rem;
                margin-top: 0.75rem;
            }

            .product-stat {
                min-width: 0;
                padding: 0.4rem 0.35rem;
                border-radius: 0.55rem;
                background: #f8fafc;
                text-align: center;
            }

            .product-stat span {
                display: block;
                color: #94a3b8;
                font-size: 0.62rem;
                font-weight: 700;
                letter-spacing: 0.03em;
                text-transform: uppercase;
            }

            .product-stat strong {
                display: block;
                margin-top: 0.12rem;
                color: #0f172a;
                font-size: 0.78rem;
                line-height: 1.25;
                word-break: break-word;
            }

            .group-meta {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 0.55rem;
                margin-bottom: 0.75rem;
            }

            .group-meta div,
            .group-card-amounts {
                min-width: 0;
                padding: 0.55rem 0.65rem;
                border-radius: 0.65rem;
                background: #f8fafc;
            }

            .group-meta span,
            .group-card-amounts span {
                display: block;
                color: #64748b;
                font-size: 0.68rem;
                font-weight: 700;
                letter-spacing: 0.03em;
                text-transform: uppercase;
            }

            .group-meta strong,
            .group-card-amounts strong {
                display: block;
                margin-top: 0.15rem;
                color: #0f172a;
                font-size: 0.9rem;
                word-break: break-word;
            }

            .group-card-amounts {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 0.45rem;
                margin-top: 0.65rem;
                padding: 0;
                background: transparent;
            }

            .group-card-amounts div {
                padding: 0.45rem 0.55rem;
                border-radius: 0.55rem;
                background: rgba(255, 255, 255, 0.8);
            }

            .bill-back-link {
                display: flex;
                width: 100%;
                justify-content: center;
                margin-top: 1.15rem;
            }

            @media (min-width: 768px) {
                .detail-list {
                    grid-template-columns: 1fr 1fr;
                    column-gap: 1.25rem;
                }

                .product-list {
                    grid-template-columns: 1fr 1fr;
                }

                .total-banner strong {
                    font-size: 1.12rem;
                }
            }
        }
    </style>

    <div class="page-content container-fluid">
        <div class="card shadow-sm">
            <div class="card-header bg-white bill-detail-header d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="card-title fw-bold text-dark mb-2 mb-md-0">
                    Details for Bill #{{ $bill->bill_no }}
                </h3>
                <div class="bill-detail-actions">
                    <a href="{{ route('vashi-market.edit', $bill->id) }}"
                        class="btn btn-outline-primary btn-sm mb-1 mb-md-0">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>

                    <a href="{{ route('vashi-market.payments.create', ['party_name' => $bill->party_name]) }}"
                        class="btn btn-outline-success btn-sm mb-1 mb-md-0">
                        <i class="fas fa-money-check-alt me-1"></i> Pay Multiple Bills
                    </a>
                </div>
            </div>

            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="bill-detail-mobile d-lg-none">
                    <section class="bill-section">
                        <h4 class="section-title">Bill Information</h4>
                        <div class="detail-list">
                            <div class="detail-item">
                                <span class="detail-label">Party Name</span>
                                <span class="detail-value">{{ $bill->party_name }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Bill Date</span>
                                <span class="detail-value">{{ \Carbon\Carbon::parse($bill->bill_date)->format('d M Y') }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Received Date</span>
                                <span class="detail-value">{{ \Carbon\Carbon::parse($bill->received_date)->format('d M Y') }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Dalal</span>
                                <span class="detail-value">{{ $bill->dalal }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Transport</span>
                                <span class="detail-value">{{ $bill->transport_name }}</span>
                            </div>
                        </div>
                        <div class="total-banner">
                            <div class="total-banner-chip">
                                <span>Total Amount</span>
                                <strong>₹{{ number_format($totalBillAmount, 2) }}</strong>
                            </div>
                        </div>
                    </section>

                    <section class="bill-section">
                        <div class="section-heading">
                            <h4 class="section-title">Payment</h4>
                            @if($bill->is_paid)
                                <span class="status-pill paid"><i class="fas fa-check-circle"></i> Paid</span>
                            @else
                                <span class="status-pill unpaid"><i class="fas fa-clock"></i> Unpaid</span>
                            @endif
                        </div>

                        @if($bill->is_paid)
                            <div class="detail-list">
                                <div class="detail-item">
                                    <span class="detail-label">Paid Date</span>
                                    <span class="detail-value">{{ $bill->paid_date ? \Carbon\Carbon::parse($bill->paid_date)->format('d M Y') : 'N/A' }}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Paid Amount</span>
                                    <span class="detail-value">₹{{ number_format((float) $bill->paid_amount, 2) }}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Payment Type</span>
                                    <span class="detail-value">{{ $bill->payment_type ?? 'N/A' }}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Cheque / Txn No</span>
                                    <span class="detail-value">{{ $bill->transaction_id ?? $bill->cheque_no ?? 'N/A' }}</span>
                                </div>
                            </div>

                            @if($difference > 0)
                                <div class="interest-note interest">
                                    <span>Interest paid</span>
                                    <span>+₹{{ number_format($difference, 2) }} ({{ number_format($differencePercent, 2) }}%)</span>
                                </div>
                            @elseif($difference < 0)
                                <div class="interest-note offer">
                                    <span>Offer received</span>
                                    <span>₹{{ number_format(abs($difference), 2) }} ({{ number_format($differencePercent, 2) }}%)</span>
                                </div>
                            @else
                                <div class="interest-note even">
                                    <span>Interest / Offer</span>
                                    <span>Exact amount</span>
                                </div>
                            @endif
                        @else
                            <p class="unpaid-note">This bill is not paid yet.</p>
                        @endif
                    </section>

                    @if ($bill->paymentAllocations->isNotEmpty())
                        <section class="bill-section">
                            <h4 class="section-title">Group Payment</h4>
                            @foreach ($bill->paymentAllocations as $allocation)
                                @php $groupPayment = $allocation->payment; @endphp
                                <div class="group-meta">
                                    <div>
                                        <span>Receipt / Cheque</span>
                                        <strong>{{ $groupPayment->receipt_no ?? $groupPayment->cheque_no ?? $groupPayment->transaction_id ?? 'N/A' }}</strong>
                                    </div>
                                    <div>
                                        <span>Group Paid Total</span>
                                        <strong>₹{{ number_format((float) $groupPayment->total_paid_amount, 2) }}</strong>
                                    </div>
                                </div>
                                <div class="group-list">
                                    @foreach ($groupPayment->allocations as $sibling)
                                        <a href="{{ route('vashi-market.showBillDetails', $sibling->vashi_market_bill_id) }}"
                                            class="group-card text-decoration-none {{ $sibling->vashi_market_bill_id === $bill->id ? 'is-current' : '' }}">
                                            <div class="group-card-top">
                                                <span class="group-card-name">Bill #{{ $sibling->bill->bill_no ?? $sibling->vashi_market_bill_id }}</span>
                                            </div>
                                            <div class="group-card-amounts">
                                                <div>
                                                    <span>Bill Amount</span>
                                                    <strong>₹{{ number_format((float) $sibling->bill_amount, 2) }}</strong>
                                                </div>
                                                <div>
                                                    <span>Paid Share</span>
                                                    <strong>₹{{ number_format((float) $sibling->allocated_amount, 2) }}</strong>
                                                </div>
                                            </div>
                                            <div class="interest-note {{ (float) $sibling->interest_difference > 0 ? 'interest' : ((float) $sibling->interest_difference < 0 ? 'offer' : 'even') }}">
                                                @if ((float) $sibling->interest_difference > 0)
                                                    <span>Interest</span>
                                                    <span>+₹{{ number_format((float) $sibling->interest_difference, 2) }} ({{ number_format((float) $sibling->interest_percentage, 2) }}%)</span>
                                                @elseif ((float) $sibling->interest_difference < 0)
                                                    <span>Offer</span>
                                                    <span>₹{{ number_format(abs((float) $sibling->interest_difference), 2) }} ({{ number_format((float) $sibling->interest_percentage, 2) }}%)</span>
                                                @else
                                                    <span>Interest / Offer</span>
                                                    <span>Exact</span>
                                                @endif
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @endforeach
                        </section>
                    @endif

                    <section class="bill-section">
                        <h4 class="section-title">Products</h4>
                        <div class="product-list">
                            @foreach($bill->products as $product)
                                <article class="product-card">
                                    <div class="product-card-top">
                                        <div>
                                            <span class="product-card-name">{{ $product->product_name }}</span>
                                            <span class="product-card-brand">{{ $product->brand_name ?: 'No brand' }}</span>
                                        </div>
                                        <span class="product-card-amount">₹{{ number_format((float) $product->product_amount, 2) }}</span>
                                    </div>
                                    <div class="product-stats">
                                        <div class="product-stat">
                                            <span>Bags</span>
                                            <strong>{{ $product->num_bags }}</strong>
                                        </div>
                                        <div class="product-stat">
                                            <span>Bag kg</span>
                                            <strong>{{ number_format((float) $product->bag_size, 2) }}</strong>
                                        </div>
                                        <div class="product-stat">
                                            <span>Weight</span>
                                            <strong>{{ number_format((float) $product->total_kg, 2) }}</strong>
                                        </div>
                                        <div class="product-stat">
                                            <span>Rate</span>
                                            <strong>{{ number_format((float) $product->rate, 2) }}</strong>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>

                    <a href="{{ route('vashi-market.index') }}" class="btn btn-secondary bill-back-link">
                        <i class="fas fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>

                <div class="bill-detail-desktop d-none d-lg-block">
                    <h4>Bill Information</h4>
                    <div class="row mb-4">
                        <div class="col-md-4"><strong>Party Name:</strong> {{ $bill->party_name }}</div>
                        <div class="col-md-4"><strong>Bill Date:</strong>
                            {{ \Carbon\Carbon::parse($bill->bill_date)->format('d F, Y') }}</div>
                        <div class="col-md-4"><strong>Received Date:</strong>
                            {{ \Carbon\Carbon::parse($bill->received_date)->format('d F, Y') }}</div>
                        <div class="col-md-4"><strong>Dalal:</strong> {{ $bill->dalal }}</div>
                        <div class="col-md-4"><strong>Transport:</strong> {{ $bill->transport_name }}</div>
                        <div class="col-md-4"><strong>Total Amount:</strong> ₹{{ number_format($bill->total_bill_amount, 2) }}
                        </div>
                    </div>

                    <hr>

                    <h4>Payment Status</h4>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <strong>Status:</strong>
                            @if($bill->is_paid)
                                <span class="badge bg-success">Paid</span>
                            @else
                                <span class="badge bg-warning text-dark">Unpaid</span>
                            @endif
                        </div>
                        @if($bill->is_paid)
                            <div class="col-md-4"><strong>Paid Date:</strong>
                                {{ $bill->paid_date ? \Carbon\Carbon::parse($bill->paid_date)->format('d F, Y') : 'N/A' }}</div>
                            <div class="col-md-4"><strong>Paid Amount:</strong> ₹{{ number_format($bill->paid_amount, 2) }}</div>
                            <div class="col-md-4"><strong>Payment Type:</strong> {{ $bill->payment_type ?? 'N/A' }}</div>
                            <div class="col-md-4"><strong>Transaction/Cheque No:</strong>
                                {{ $bill->transaction_id ?? $bill->cheque_no ?? 'N/A' }}</div>
                            <div class="col-md-4">
                                <strong>Interest / Offer:</strong>
                                @if($difference > 0)
                                    <span class="text-danger fw-semibold">
                                        Interest Paid: +₹{{ number_format($difference, 2) }}
                                        ({{ number_format($differencePercent, 2) }}%)
                                    </span>
                                @elseif($difference < 0)
                                    <span class="text-success fw-semibold">
                                        Offer Received: ₹{{ number_format(abs($difference), 2) }}
                                        ({{ number_format($differencePercent, 2) }}%)
                                    </span>
                                @else
                                    <span class="text-muted">No interest or offer (0.00%)</span>
                                @endif
                            </div>
                        @endif
                    </div>

                    @if ($bill->paymentAllocations->isNotEmpty())
                        <div class="alert alert-light border mb-4">
                            <h5 class="mb-3">Group Payment Link</h5>
                            @foreach ($bill->paymentAllocations as $allocation)
                                @php $groupPayment = $allocation->payment; @endphp
                                <div class="mb-3">
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <strong>Receipt / Cheque:</strong>
                                            {{ $groupPayment->receipt_no ?? $groupPayment->cheque_no ?? $groupPayment->transaction_id ?? 'N/A' }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Group Paid Total:</strong>
                                            ₹{{ number_format((float) $groupPayment->total_paid_amount, 2) }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>This Bill Share:</strong>
                                            ₹{{ number_format((float) $allocation->allocated_amount, 2) }}
                                        </div>
                                    </div>
                                    <div class="table-responsive mt-3">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Bill No</th>
                                                    <th>Bill Amount</th>
                                                    <th>Paid Share</th>
                                                    <th>Interest / Offer</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($groupPayment->allocations as $sibling)
                                                    <tr @class(['table-success' => $sibling->vashi_market_bill_id === $bill->id])>
                                                        <td>
                                                            <a href="{{ route('vashi-market.showBillDetails', $sibling->vashi_market_bill_id) }}">
                                                                {{ $sibling->bill->bill_no ?? ('#'.$sibling->vashi_market_bill_id) }}
                                                            </a>
                                                        </td>
                                                        <td>₹{{ number_format((float) $sibling->bill_amount, 2) }}</td>
                                                        <td>₹{{ number_format((float) $sibling->allocated_amount, 2) }}</td>
                                                        <td>
                                                            @if ((float) $sibling->interest_difference > 0)
                                                                <span class="text-danger">
                                                                    +₹{{ number_format((float) $sibling->interest_difference, 2) }}
                                                                    ({{ number_format((float) $sibling->interest_percentage, 2) }}%)
                                                                </span>
                                                            @elseif ((float) $sibling->interest_difference < 0)
                                                                <span class="text-success">
                                                                    ₹{{ number_format(abs((float) $sibling->interest_difference), 2) }}
                                                                    ({{ number_format((float) $sibling->interest_percentage, 2) }}%)
                                                                </span>
                                                            @else
                                                                Exact
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <hr>

                    <h4>Products</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Product Name</th>
                                    <th>Brand</th>
                                    <th>No. of Bags</th>
                                    <th>Bag Size (kg)</th>
                                    <th>Total Weight (kg)</th>
                                    <th>Rate</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bill->products as $product)
                                    <tr>
                                        <td>{{ $product->product_name }}</td>
                                        <td>{{ $product->brand_name ?? '-' }}</td>
                                        <td>{{ $product->num_bags }}</td>
                                        <td>{{ number_format($product->bag_size, 2) }}</td>
                                        <td>{{ number_format($product->total_kg, 2) }}</td>
                                        <td>₹{{ number_format($product->rate, 2) }}</td>
                                        <td>₹{{ number_format($product->product_amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('vashi-market.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
