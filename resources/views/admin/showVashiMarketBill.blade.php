@extends('layouts.app')

@section('content')
    <style>
        /* Fix header overlap on mobile */
        @media (max-width: 768px) {
            .page-content {
                margin-top: 70px !important;
                /* adjust if header height differs */
            }
        }

        /* Make the table scrollable on smaller screens */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-responsive table {
            min-width: 800px;
            /* ensures columns don’t squish too much */
        }
    </style>

    <div class="page-content container-fluid">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="card-title fw-bold text-dark mb-2 mb-md-0">
                    Details for Bill #{{ $bill->bill_no }}
                </h3>
                <div>
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
                {{-- Success Message --}}
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                {{-- Bill Information --}}
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

                {{-- Payment Details --}}
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
                        @php
                            $difference = (float) ($bill->payment_difference ?? 0);
                            $totalBillAmount = (float) $bill->total_bill_amount;
                            $differencePercent = $totalBillAmount > 0
                                ? (abs($difference) / $totalBillAmount) * 100
                                : 0;
                        @endphp
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

                {{-- Products Table --}}
                <h4>Products</h4>
                <div class="table-responsive"> {{-- 👈 Added wrapper --}}
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
@endsection