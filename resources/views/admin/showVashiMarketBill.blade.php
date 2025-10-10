@extends('layouts.app')

@section('content')
<div class="page-content container-fluid">
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h3 class="card-title fw-bold text-dark mb-0">
                Details for Bill #{{ $bill->bill_no }}
            </h3>
            <div>
                <a href="" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-money-check-alt me-1"></i> Update Payment
                </a>
            </div>
        </div>

        <div class="card-body">
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
                @endif
            </div>
            <hr>

            {{-- Products Table --}}
            <h4>Products</h4>
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

            <div class="mt-4">
                <a href="{{ route('vashi-market.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>
@endsection