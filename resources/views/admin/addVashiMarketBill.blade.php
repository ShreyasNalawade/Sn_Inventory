@extends('layouts.app')

@section('styles')
    {{-- In a real application, these would likely be in a compiled CSS file --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    {{-- You can link your custom CSS files here --}}
    {{--
    <link rel="stylesheet" href="/assets/css/main.css" /> --}}
    {{--
    <link rel="stylesheet" href="/assets/css/new-main-price.css" /> --}}
    <style>
        body {
            font-family: "Inter", sans-serif;
            background-color: #f3f4f6;
        }

        .form-section {
            margin-bottom: 1.5rem;
        }

        .form-section h4 {
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }

        .payment-details-section {
            display: none;
        }

        .payment-input {
            display: none;
        }

        .product-section-header {
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: 0.85rem;
        }

        .product-section-header h4 {
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: 0;
            font-weight: 700;
        }

        .product-list-wrap {
            background: #eef2f7;
            border: 1px solid #d8e0ea;
            border-radius: 16px;
            padding: 12px;
            margin-top: 4px;
        }

        #product-container {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        #product-container .product-item {
            border: 1px solid #cfd8e3 !important;
            padding: 0 !important;
            border-radius: 14px !important;
            position: relative;
            margin: 0;
            background: #ffffff !important;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        #product-container .product-item .delete-btn {
            flex-shrink: 0;
            background: #fff;
            color: #dc2626;
            border-color: #fecaca;
            font-weight: 600;
        }

        #product-container .product-header {
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            padding: 12px 14px;
            border-bottom: none;
            margin: 0;
            color: #fff;
        }

        #product-container .product-item:nth-child(even) .product-header {
            background: linear-gradient(135deg, #0369a1, #0284c7);
        }

        .product-number-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.7rem;
            height: 1.7rem;
            padding: 0 0.35rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.22);
            color: #fff;
            font-size: 0.8rem;
            font-weight: 700;
            line-height: 1;
        }

        #product-container .product-heading {
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
        }

        #product-container .product-body {
            padding: 12px 14px 6px;
            background: #fff;
        }

        .product-group {
            background: #f8fafc;
            border: 1px solid #e8eef5;
            border-radius: 10px;
            padding: 10px 12px 12px;
            margin-bottom: 10px;
        }

        .product-group-title {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 8px;
        }

        .product-footer {
            background: #f0fdf4;
            border-top: 1px dashed #86efac;
            padding: 12px 14px 14px;
        }

        .product-footer .form-label {
            font-weight: 600;
            color: #166534;
        }

        .product-footer .product-amount {
            font-weight: 700;
            background: #fff;
            border-color: #86efac;
        }

        .product-empty-state {
            display: none;
            text-align: center;
            padding: 1.5rem 1rem;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            background: #fff;
            color: #64748b;
            margin-top: 4px;
        }

        #product-container:empty + .product-empty-state,
        #product-container:not(:has(.product-item)) + .product-empty-state {
            display: block;
        }

        @media (max-width: 576px) {
            .product-list-wrap {
                padding: 10px;
            }

            #product-container {
                gap: 18px;
            }

            #product-container .product-header {
                padding: 11px 12px;
            }

            #product-container .product-item .delete-btn .delete-label {
                display: none;
            }
        }

        .btn-continue {
            background-color: #ff6347;
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 30px;
            font-size: 1.2rem;
            box-shadow: 0 4px 10px rgba(255, 99, 71, 0.4);
        }

        .payment-difference-card {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            background-color: #f8fafc;
            padding: 1rem;
        }

        .payment-difference-card .form-control {
            font-weight: 600;
        }

        .payment-difference-card .form-control.difference-interest {
            color: #dc2626;
            border-color: #f87171;
            background-color: #fef2f2;
        }

        .payment-difference-card .form-control.difference-offer {
            color: #15803d;
            border-color: #86efac;
            background-color: #f0fdf4;
        }

        .payment-difference-card .form-control.difference-even {
            color: #475569;
            border-color: #cbd5e1;
            background-color: #fff;
        }

        #payment-difference-status {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        #payment-difference-status.status-interest {
            color: #dc2626;
        }

        #payment-difference-status.status-offer {
            color: #15803d;
        }

        #payment-difference-status.status-even {
            color: #475569;
        }
    </style>
    <style>
        #pageLoader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.85);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
    </style>

@endsection

@section('content')
    <style>
        .product-list-wrap {
            background: #eef2f7;
            border: 1px solid #d8e0ea;
            border-radius: 16px;
            padding: 12px;
            margin-top: 4px;
        }
        #product-container { display: flex; flex-direction: column; gap: 16px; }
        #product-container .product-item {
            border: 1px solid #cfd8e3 !important;
            padding: 0 !important;
            border-radius: 14px !important;
            background: #fff !important;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }
        #product-container .product-header {
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            padding: 12px 14px;
            color: #fff;
        }
        #product-container .product-item:nth-child(even) .product-header {
            background: linear-gradient(135deg, #0369a1, #0284c7);
        }
        #product-container .product-heading { color: #fff; font-weight: 700; font-size: 1rem; margin: 0; }
        .product-number-badge {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 1.7rem; height: 1.7rem; border-radius: 999px;
            background: rgba(255,255,255,0.22); color: #fff; font-weight: 700; font-size: 0.8rem;
        }
        #product-container .delete-btn {
            background: #fff; color: #dc2626; border: 1px solid #fecaca; font-weight: 600;
        }
        #product-container .product-body { padding: 12px 14px 6px; background: #fff; }
        .product-group {
            background: #f8fafc; border: 1px solid #e8eef5; border-radius: 10px;
            padding: 10px 12px 12px; margin-bottom: 10px;
        }
        .product-group-title {
            font-size: 0.72rem; font-weight: 700; letter-spacing: 0.06em;
            text-transform: uppercase; color: #64748b; margin-bottom: 8px;
        }
        .product-footer {
            background: #f0fdf4; border-top: 1px dashed #86efac; padding: 12px 14px 14px;
        }
        .product-footer .form-label { font-weight: 600; color: #166534; }
        .product-footer .product-amount { font-weight: 700; background: #fff; border-color: #86efac; }
        .product-empty-state {
            display: none; text-align: center; padding: 1.5rem 1rem;
            border: 1px dashed #cbd5e1; border-radius: 12px; background: #fff; color: #64748b;
        }
        #product-container:empty + .product-empty-state { display: block; }
        @media (max-width: 576px) {
            #product-container { gap: 18px; }
            #product-container .delete-btn .delete-label { display: none; }
        }
    </style>
    <div class="page-content container-fluid">
        <div class="card shadow-sm">
            <div class="card-header bg-white text-center py-3">
                <h3 class="card-title fw-bold text-dark mb-0">
                    Add Vashi Market Bill shreyash
                </h3>
            </div>
            <div class="card-body">

                <a href="{{ route('vashi-market.index') }}" class="btn btn-outline-secondary mb-3">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>

                {{-- Display Validation Errors --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Display Success Message --}}
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Display Error Message --}}
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <form id="bill-form" action="{{ route('vashi-market.store') }}" method="POST"> {{-- Example route
                    --}}
                    @csrf
                    <div class="form-section">
                        <h4>Bill Details</h4>
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-4">
                                <label for="bill-date" class="form-label">Bill Date</label>
                                <input type="date" class="form-control" id="bill-date" name="bill_date" required />
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label for="received-date" class="form-label">Received Date</label>
                                <input type="date" class="form-control" id="received-date" name="received_date" required />
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label for="bill-no" class="form-label">Bill No</label>
                                <input type="text" class="form-control" id="bill-no" name="bill_no" required />
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label for="party-name" class="form-label">Party Name</label>
                                <input type="text" class="form-control" id="party-name" name="party_name" required />
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label for="dalal" class="form-label">Dalal</label>
                                <input type="text" class="form-control" id="dalal" name="dalal" required />
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label for="transport-name" class="form-label">Transport Name</label>
                                <input type="text" class="form-control" id="transport-name" name="transport_name"
                                    required />
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="product-section-header d-flex justify-content-between align-items-center">
                            <h4>Product Details</h4>
                            <button type="button" class="btn btn-primary btn-sm" id="add-product-btn"
                                style="min-width: 130px;">
                                <i class="fas fa-plus me-1"></i> Add Product <span id="product-count-badge"
                                    class="badge bg-light text-dark ms-1"></span>
                            </button>
                        </div>
                        <div class="product-list-wrap">
                            <div id="product-container"></div>
                            <div class="product-empty-state">
                                <i class="fas fa-box-open mb-2 d-block" style="font-size: 1.5rem;"></i>
                                No products added yet. Click <strong>Add Product</strong> to start.
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h4>Summary</h4>
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-4">
                                <label for="total-bill-amount" class="form-label">Total Bill Amount</label>
                                <input type="number" class="form-control" id="total-bill-amount" name="total_bill_amount"
                                    step="0.001" required />
                            </div>
                        </div>
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" id="paid-switch" name="is_paid" value="1" />
                        <label class="form-check-label" for="paid-switch">Mark as Paid</label>
                    </div>

                    <div class="payment-details-section">
                        <h4>Payment Details</h4>
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-4">
                                <label for="payment-type" class="form-label">Type of Payment</label>
                                <select class="form-select" id="payment-type" name="payment_type">
                                    <option value="">Select Payment Type</option>
                                    <option value="Gpay">Gpay</option>
                                    <option value="Cheque">Cheque</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Account Transfer">Account Transfer</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-4 payment-input" id="transaction-id-input">
                                <label for="transaction-id" class="form-label">Transaction ID</label>
                                <input type="text" class="form-control" id="transaction-id" name="transaction_id" />
                            </div>
                            <div class="col-md-6 col-lg-4 payment-input" id="cheque-no-input">
                                <label for="cheque-no" class="form-label">Cheque No</label>
                                <input type="text" class="form-control" id="cheque-no" name="cheque_no" />
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label for="receipt-no" class="form-label">Receipt Number</label>
                                <input type="text" class="form-control" id="receipt-no" name="receipt_no" />
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label for="paid-date" class="form-label">Paid Date</label>
                                <input type="date" class="form-control" id="paid-date" name="paid_date" />
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label for="paid-amount" class="form-label">Paid Amount</label>
                                <input type="number" class="form-control" id="paid-amount" name="paid_amount" step="0.01" />
                            </div>
                            <div class="col-12">
                                <div class="payment-difference-card">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-6 col-lg-4">
                                            <label for="payment-difference" class="form-label">Interest / Offer Difference</label>
                                            <input type="number" class="form-control difference-even"
                                                id="payment-difference" name="payment_difference" step="0.01"
                                                value="0.00" />
                                        </div>
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label">Percentage</label>
                                            <input type="text" class="form-control" id="payment-difference-percentage"
                                                value="0.00%" readonly />
                                        </div>
                                    </div>
                                    <span id="payment-difference-status" class="status-even">
                                        Enter the total bill amount and paid amount to calculate.
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-center">
                        <button type="submit" id="saveBillBtn"
                            class="btn btn-continue bg-primary text-white col-12 col-md-6 position-relative">
                            <span id="saveBtnText"><i class="fas fa-save me-1"></i> Save</span>
                            <span id="saveBtnLoader" class="spinner-border spinner-border-sm text-light ms-2 d-none"
                                role="status"></span>
                        </button>

                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- This template is used by the JavaScript to create new product rows --}}
    <template id="product-template">
        <div class="product-item">
            <div class="product-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="product-number-badge">1</span>
                    <h5 class="product-heading mb-0">Product 1</h5>
                </div>
                <button type="button" class="btn btn-sm delete-btn" title="Remove product">
                    <i class="fas fa-trash-alt"></i><span class="delete-label ms-1">Remove</span>
                </button>
            </div>
            <div class="product-body">
                <div class="product-group">
                    <div class="product-group-title">Product info</div>
                    <div class="row g-2">
                        <div class="col-12 col-md-6 col-lg-4">
                            <label class="form-label">Product</label>
                            <input type="text" class="form-control product-name" name="products[][product_name]" required />
                        </div>
                        <div class="col-12 col-md-6 col-lg-4">
                            <label class="form-label">Brand Name</label>
                            <input type="text" class="form-control brand-name" name="products[][brand_name]" />
                        </div>
                    </div>
                </div>
                <div class="product-group">
                    <div class="product-group-title">Quantity</div>
                    <div class="row g-2">
                        <div class="col-6 col-lg-4">
                            <label class="form-label">No. of Bags</label>
                            <input type="number" class="form-control num-bags" name="products[][num_bags]" step="1" required />
                        </div>
                        <div class="col-6 col-lg-4">
                            <label class="form-label">Bag Size (kg)</label>
                            <input type="number" class="form-control bag-size" name="products[][bag_size]" step="0.01" required />
                        </div>
                        <div class="col-12 col-lg-4">
                            <label class="form-label">Total Kg</label>
                            <input type="number" class="form-control total-kg" name="products[][total_kg]" step="0.01" required />
                        </div>
                    </div>
                </div>
            </div>
            <div class="product-footer">
                <div class="row g-2">
                    <div class="col-6 col-lg-4">
                        <label class="form-label">Rate</label>
                        <input type="number" class="form-control product-rate" name="products[][rate]" step="0.01" required />
                    </div>
                    <div class="col-6 col-lg-4">
                        <label class="form-label">Total Amount</label>
                        <input type="number" class="form-control product-amount" name="products[][product_amount]" step="0.01"
                            value="0.00" required />
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Full Page Loader -->
    <div id="pageLoader" style="display:none;">
        <div class="spinner-border text-primary" style="width:3rem; height:3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

@endsection

@section('scripts')
    {{-- In a real app, these would be in the main layout or a compiled JS file --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const paidSwitch = document.getElementById("paid-switch");
            const paymentDetailsSection = document.querySelector(".payment-details-section");
            const paymentTypeSelect = document.getElementById("payment-type");
            const transactionIdInput = document.getElementById("transaction-id-input");
            const chequeNoInput = document.getElementById("cheque-no-input");
            const billForm = document.getElementById("bill-form");
            const addProductBtn = document.getElementById("add-product-btn");
            const productContainer = document.getElementById("product-container");
            const productTemplate = document.getElementById("product-template");
            const totalBillAmountInput = document.getElementById('total-bill-amount');
            const paidAmountInput = document.getElementById('paid-amount');
            const paymentDifferenceInput = document.getElementById('payment-difference');
            const paymentDifferencePercentage = document.getElementById('payment-difference-percentage');
            const paymentDifferenceStatus = document.getElementById('payment-difference-status');
            const productCountBadge = document.getElementById('product-count-badge');

            // Set initial state for payment section
            paymentDetailsSection.style.display = paidSwitch.checked ? "block" : "none";

            // Toggle payment details section
            paidSwitch.addEventListener("change", () => {
                paymentDetailsSection.style.display = paidSwitch.checked ? "block" : "none";
            });

            const formatAmount = (amount) => {
                return Math.abs(amount).toFixed(2);
            };

            const updateDifferenceDisplay = (difference, totalBillAmount) => {
                const percentage = totalBillAmount > 0
                    ? (Math.abs(difference) / totalBillAmount) * 100
                    : 0;

                paymentDifferencePercentage.value = `${percentage.toFixed(2)}%`;
                paymentDifferenceInput.classList.remove(
                    'difference-interest',
                    'difference-offer',
                    'difference-even'
                );
                paymentDifferenceStatus.classList.remove(
                    'status-interest',
                    'status-offer',
                    'status-even'
                );

                if (difference > 0) {
                    paymentDifferenceInput.classList.add('difference-interest');
                    paymentDifferenceStatus.classList.add('status-interest');
                    paymentDifferenceStatus.textContent =
                        `Interest paid: +₹${formatAmount(difference)} (${percentage.toFixed(2)}%)`;
                } else if (difference < 0) {
                    paymentDifferenceInput.classList.add('difference-offer');
                    paymentDifferenceStatus.classList.add('status-offer');
                    paymentDifferenceStatus.textContent =
                        `Offer received: ₹${formatAmount(difference)} (${percentage.toFixed(2)}%)`;
                } else {
                    paymentDifferenceInput.classList.add('difference-even');
                    paymentDifferenceStatus.classList.add('status-even');
                    paymentDifferenceStatus.textContent = 'No interest or offer difference (0.00%).';
                }
            };

            const calculatePaymentDifference = () => {
                const totalBillAmount = parseFloat(totalBillAmountInput.value);
                const paidAmount = parseFloat(paidAmountInput.value);

                if (!Number.isFinite(totalBillAmount) || !Number.isFinite(paidAmount) || totalBillAmount <= 0) {
                    paymentDifferenceInput.value = '0.00';
                    updateDifferenceDisplay(0, 0);
                    paymentDifferenceStatus.textContent =
                        'Enter a valid total bill amount and paid amount to calculate.';
                    return;
                }

                const difference = paidAmount - totalBillAmount;
                paymentDifferenceInput.value = difference.toFixed(2);
                updateDifferenceDisplay(difference, totalBillAmount);
            };

            const updatePercentageFromEditedDifference = () => {
                const totalBillAmount = parseFloat(totalBillAmountInput.value);
                const difference = parseFloat(paymentDifferenceInput.value);

                if (!Number.isFinite(totalBillAmount) || totalBillAmount <= 0 || !Number.isFinite(difference)) {
                    paymentDifferencePercentage.value = '0.00%';
                    return;
                }

                updateDifferenceDisplay(difference, totalBillAmount);
            };

            totalBillAmountInput.addEventListener('input', calculatePaymentDifference);
            paidAmountInput.addEventListener('input', calculatePaymentDifference);
            paymentDifferenceInput.addEventListener('input', updatePercentageFromEditedDifference);

            // Toggle payment-specific inputs
            paymentTypeSelect.addEventListener("change", (e) => {
                const value = e.target.value;
                transactionIdInput.style.display = "none";
                chequeNoInput.style.display = "none";

                if (value === "Gpay" || value === "Account Transfer") {
                    transactionIdInput.style.display = "block";
                } else if (value === "Cheque") {
                    chequeNoInput.style.display = "block";
                }
            });

            const renumberProducts = () => {
                const productItems = document.querySelectorAll("#product-container .product-item");
                productItems.forEach((item, index) => {
                    const productNumber = index + 1;
                    item.querySelector(".product-number-badge").textContent = productNumber;
                    item.querySelector(".product-heading").textContent = `Product ${productNumber}`;
                    // Update array indices in name attributes
                    item.querySelectorAll('[name^="products["]').forEach(input => {
                        input.name = input.name.replace(/products\[\d*\]/, `products[${index}]`);
                    });
                });
                productCountBadge.textContent = productItems.length;
            };

            // Add a new product section
            const addProduct = () => {
                const clone = productTemplate.content.cloneNode(true);
                const productItem = clone.querySelector(".product-item");
                const numBagsInput = productItem.querySelector(".num-bags");
                const bagSizeInput = productItem.querySelector(".bag-size");
                const totalKgInput = productItem.querySelector(".total-kg");
                const productRateInput = productItem.querySelector(".product-rate");
                const productAmountInput = productItem.querySelector(".product-amount");

                const calculateProductAmount = () => {
                    const totalKg = parseFloat(totalKgInput.value) || 0;
                    const rate = parseFloat(productRateInput.value) || 0;

                    const productAmount = totalKg * rate;
                    productAmountInput.value = productAmount.toFixed(2);
                };

                // When a user edits total kg or rate, calculate the product amount.
                totalKgInput.addEventListener("input", calculateProductAmount);
                productRateInput.addEventListener("input", calculateProductAmount);

                productItem
                    .querySelector(".delete-btn")
                    .addEventListener("click", () => {
                        productItem.remove();
                        renumberProducts();
                    });

                productContainer.appendChild(productItem);
                renumberProducts();
            };

            addProductBtn.addEventListener("click", addProduct);

            // Override form submission to show confirmation first.
            billForm.addEventListener("submit", (e) => {
                e.preventDefault(); // Stop the form from submitting immediately

                // This part is for frontend confirmation only. 
                // The actual data will be sent to the server when the user confirms.
                Swal.fire({
                    title: 'Confirm Bill Details',
                    text: "Are you sure you want to save this bill?",
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, save it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const btn = document.getElementById('saveBillBtn');
                        const btnText = document.getElementById('saveBtnText');
                        const btnLoader = document.getElementById('saveBtnLoader');
                        const pageLoader = document.getElementById('pageLoader');

                        // Disable button and show spinner
                        btn.disabled = true;
                        btnText.textContent = 'Saving...';
                        btnLoader.classList.remove('d-none');

                        // Show full-page loader
                        pageLoader.style.display = 'flex';

                        // Now submit the form
                        billForm.submit();
                    }
                });

            });

            // Add one product section on initial load
            addProduct();
        });
    </script>
@endsection