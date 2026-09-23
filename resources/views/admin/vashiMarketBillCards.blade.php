@foreach ($bills as $bill)
    @php
        $difference = (float) ($bill->payment_difference ?? 0);
        $totalBillAmount = (float) ($bill->total_bill_amount ?? 0);
        $differencePercent = $totalBillAmount > 0 ? (abs($difference) / $totalBillAmount) * 100 : 0;
    @endphp

    <a href="{{ route('vashi-market.showBillDetails', $bill->id) }}" class="data-card-link text-decoration-none">
        <div class="card data-card bill-card {{ $bill->is_paid ? 'bill-card-paid' : 'bill-card-unpaid' }}">
            <div class="card-body">
                <div class="bill-card-header">
                    <div class="bill-card-title-wrap">
                        <span class="bill-no-label">Bill No</span>
                        <span class="bill-no">{{ $bill->bill_no }}</span>
                    </div>
                    @if ($bill->is_paid)
                        <span class="bill-status-badge paid">
                            <i class="fas fa-check-circle"></i> Paid
                        </span>
                    @else
                        <span class="bill-status-badge unpaid">
                            <i class="fas fa-clock"></i> Unpaid
                        </span>
                    @endif
                </div>

                <ul class="list-unstyled bill-card-meta mb-0">
                    <li class="d-flex align-items-start">
                        <i class="fas fa-calendar-alt fa-fw text-muted mt-1"></i>
                        <div>
                            <strong>Bill Date:</strong>
                            <span class="bill-date">{{ \Carbon\Carbon::parse($bill->bill_date)->format('d/m/Y') }}</span>
                        </div>
                    </li>
                    <li class="d-flex align-items-start">
                        <i class="fas fa-user-tie fa-fw text-muted mt-1"></i>
                        <div>
                            <strong>Party:</strong>
                            <span class="party-name">{{ $bill->party_name }}</span>
                        </div>
                    </li>
                    <li class="d-flex align-items-start">
                        <i class="fas fa-box fa-fw text-muted mt-1"></i>
                        <div>
                            <strong>Products:</strong>
                            <span class="product-name">{{ $bill->products->pluck('product_name')->implode(', ') }}</span>
                        </div>
                    </li>
                </ul>

                <div class="bill-card-footer">
                    <div class="bill-amount-grid">
                        <div class="bill-amount-cell">
                            <span class="amount-label">Total</span>
                            <span class="amount-value">₹{{ number_format($totalBillAmount, 2) }}</span>
                        </div>

                        @if ($bill->is_paid)
                            <div class="bill-amount-cell">
                                <span class="amount-label">Paid</span>
                                <span class="amount-value paid-amount">₹{{ number_format((float) $bill->paid_amount, 2) }}</span>
                            </div>
                        @else
                            <div class="bill-amount-cell">
                                <span class="amount-label">Status</span>
                                <span class="amount-value pending-amount">Pending</span>
                            </div>
                        @endif
                    </div>

                    @if ($bill->is_paid)
                        @if ($difference > 0)
                            <div class="interest-bar interest">
                                <span class="interest-bar-label">
                                    <i class="fas fa-arrow-up"></i> Interest
                                </span>
                                <span class="interest-bar-value">
                                    +₹{{ number_format($difference, 2) }}
                                    <small>{{ number_format($differencePercent, 2) }}%</small>
                                </span>
                            </div>
                        @elseif ($difference < 0)
                            <div class="interest-bar offer">
                                <span class="interest-bar-label">
                                    <i class="fas fa-tag"></i> Offer
                                </span>
                                <span class="interest-bar-value">
                                    ₹{{ number_format(abs($difference), 2) }}
                                    <small>{{ number_format($differencePercent, 2) }}%</small>
                                </span>
                            </div>
                        @else
                            <div class="interest-bar even">
                                <span class="interest-bar-label">
                                    <i class="fas fa-check"></i> Settled
                                </span>
                                <span class="interest-bar-value">Exact amount</span>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </a>
@endforeach
