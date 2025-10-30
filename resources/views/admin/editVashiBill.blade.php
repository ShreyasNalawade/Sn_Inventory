@extends('layouts.app')

@section('content')
    <div class="page-content container-fluid mt-5 mb-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="card-title fw-bold text-dark mb-2 mb-md-0">
                    Edit Bill #{{ $bill->bill_no }}
                </h3>
                <a href="{{ route('vashi-market.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>

            <div class="card-body">
                {{-- Alerts --}}
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @elseif(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('vashi-market.update', $bill->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Bill Info --}}
                    <h4 class="fw-bold text-primary">Bill Information</h4>
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <label>Bill No</label>
                            <input type="text" name="bill_no" class="form-control"
                                value="{{ old('bill_no', $bill->bill_no) }}" readonly>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Bill Date</label>
                            <input type="date" name="bill_date" class="form-control"
                                value="{{ old('bill_date', $bill->bill_date) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Received Date</label>
                            <input type="date" name="received_date" class="form-control"
                                value="{{ old('received_date', $bill->received_date) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Party Name</label>
                            <input type="text" name="party_name" class="form-control"
                                value="{{ old('party_name', $bill->party_name) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Dalal</label>
                            <input type="text" name="dalal" class="form-control" value="{{ old('dalal', $bill->dalal) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Transport Name</label>
                            <input type="text" name="transport_name" class="form-control"
                                value="{{ old('transport_name', $bill->transport_name) }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Total Bill Amount</label>
                            <input type="number" step="0.01" name="total_bill_amount" class="form-control"
                                value="{{ old('total_bill_amount', $bill->total_bill_amount) }}">
                        </div>
                    </div>

                    <hr>

                    {{-- Payment Info --}}
                    <h4 class="fw-bold text-primary">Payment Details</h4>
                    <div class="row mb-4 align-items-center">
                        <div class="col-md-4 mb-3 d-flex align-items-center">
                            <label class="me-3">Is Paid</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="isPaidToggle" name="is_paid" value="1"
                                    {{ $bill->is_paid ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>

                    <div id="payment-section" style="{{ $bill->is_paid ? '' : 'display:none;' }}">
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <label>Paid Date</label>
                                <input type="date" name="paid_date" class="form-control"
                                    value="{{ old('paid_date', $bill->paid_date) }}">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Paid Amount</label>
                                <input type="number" step="0.01" name="paid_amount" class="form-control"
                                    value="{{ old('paid_amount', $bill->paid_amount) }}">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Payment Type</label>
                                <input type="text" name="payment_type" class="form-control"
                                    value="{{ old('payment_type', $bill->payment_type) }}">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Transaction ID / Cheque No</label>
                                <input type="text" name="transaction_id" class="form-control"
                                    value="{{ old('transaction_id', $bill->transaction_id ?? $bill->cheque_no) }}">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Receipt No</label>
                                <input type="text" name="receipt_no" class="form-control"
                                    value="{{ old('receipt_no', $bill->receipt_no) }}">
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- Product Details --}}
                    <h4 class="fw-bold text-primary">Products</h4>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered" id="products-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Product Name</th>
                                    <th>Brand</th>
                                    <th>No. of Bags</th>
                                    <th>Bag Size (kg)</th>
                                    <th>Total Weight (kg)</th>
                                    <th>Rate</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bill->products as $index => $product)
                                    <tr>
                                        <td><input type="text" name="products[{{ $index }}][product_name]"
                                                class="form-control form-control-lg" value="{{ $product->product_name }}"></td>
                                        <td><input type="text" name="products[{{ $index }}][brand_name]"
                                                class="form-control form-control-lg" value="{{ $product->brand_name }}"></td>
                                        <td><input type="number" name="products[{{ $index }}][num_bags]"
                                                class="form-control form-control-lg" value="{{ $product->num_bags }}"></td>
                                        <td><input type="number" step="0.01" name="products[{{ $index }}][bag_size]"
                                                class="form-control form-control-lg" value="{{ $product->bag_size }}"></td>
                                        <td><input type="number" step="0.01" name="products[{{ $index }}][total_kg]"
                                                class="form-control form-control-lg" value="{{ $product->total_kg }}"></td>
                                        <td><input type="number" step="0.01" name="products[{{ $index }}][rate]"
                                                class="form-control form-control-lg" value="{{ $product->rate }}"></td>
                                        <td><input type="number" step="0.01" name="products[{{ $index }}][product_amount]"
                                                class="form-control form-control-lg" value="{{ $product->product_amount }}">
                                        </td>
                                        <td><button type="button" class="btn btn-danger btn-sm remove-row"><i
                                                    class="fas fa-trash"></i></button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-product">
                        <i class="fas fa-plus"></i> Add Product
                    </button>

                    <div class="mt-4">
                        <button type="submit" id="updateBillBtn" class="btn btn-success position-relative">
                            <span id="btnText"><i class="fas fa-save me-1"></i> Update Bill</span>
                            <span id="btnLoader" class="spinner-border spinner-border-sm text-light ms-2 d-none"
                                role="status"></span>
                        </button>

                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Full Page Loader -->
    <div id="pageLoader">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>


    {{-- JS Section --}}
    <script>
        // Toggle Payment Section
        document.getElementById('isPaidToggle').addEventListener('change', function () {
            const paymentSection = document.getElementById('payment-section');
            if (this.checked) {
                paymentSection.style.display = 'block';
            } else {
                paymentSection.style.display = 'none';
            }
        });

        // Add New Product Row
        document.getElementById('add-product').addEventListener('click', function () {
            const tableBody = document.querySelector('#products-table tbody');
            const index = tableBody.children.length;
            const row = document.createElement('tr');
            row.innerHTML = `
                                                                    <td><input type="text" name="products[${index}][product_name]" class="form-control form-control-lg"></td>
                                                                    <td><input type="text" name="products[${index}][brand_name]" class="form-control form-control-lg"></td>
                                                                    <td><input type="number" name="products[${index}][num_bags]" class="form-control form-control-lg"></td>
                                                                    <td><input type="number" step="0.01" name="products[${index}][bag_size]" class="form-control form-control-lg"></td>
                                                                    <td><input type="number" step="0.01" name="products[${index}][total_kg]" class="form-control form-control-lg"></td>
                                                                    <td><input type="number" step="0.01" name="products[${index}][rate]" class="form-control form-control-lg"></td>
                                                                    <td><input type="number" step="0.01" name="products[${index}][product_amount]" class="form-control form-control-lg"></td>
                                                                    <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>
                                                                `;
            tableBody.appendChild(row);
        });

        // Remove Product Row
        document.addEventListener('click', function (e) {
            if (e.target.closest('.remove-row')) {
                e.target.closest('tr').remove();
            }
        });

    </script>

    {{-- CSS for mobile optimization --}}
    <style>
        /* Full-page overlay loader */
        #pageLoader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            z-index: 9999;
            display: none;
            justify-content: center;
            align-items: center;
        }
    </style>

    <style>
        /* Make inputs bigger and more visible on small screens */
        #products-table input {
            min-width: 140px;
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            #products-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            #products-table input {
                width: 120px;
                font-size: 0.95rem;
            }
        }

        .form-check-input {
            width: 50px;
            height: 25px;
            cursor: pointer;
        }
    </style>
    <script>
        document.querySelector('form').addEventListener('submit', function (e) {
            const btn = document.getElementById('updateBillBtn');
            const btnText = document.getElementById('btnText');
            const btnLoader = document.getElementById('btnLoader');
            const pageLoader = document.getElementById('pageLoader');

            // Disable the button and show the loader
            btn.disabled = true;
            btnText.textContent = 'Updating...';
            btnLoader.classList.remove('d-none');

            // Show full-page loader
            pageLoader.style.display = 'flex';
        });
    </script>

@endsection