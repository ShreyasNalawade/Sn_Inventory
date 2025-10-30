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

        .product-item {
            border: 1px solid #e2e8f0;
            padding: 1rem;
            border-radius: 0.75rem;
            position: relative;
            margin-bottom: 1rem;
            background-color: #fff;
        }

        .product-item .delete-btn {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            z-index: 10;
        }

        .product-header {
            background-color: #f8f9fa;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e2e8f0;
            margin: -1rem -1rem 1rem -1rem;
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
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
    <div class="page-content container-fluid">
        <div class="card shadow-sm">
            <div class="card-header bg-white text-center py-3">
                <h3 class="card-title fw-bold text-dark mb-0">
                    Add Vashi Market Bill
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
                        <div class="d-flex justify-content-between align-items-center">
                            <h4>Product Details</h4>
                            <button type="button" class="btn btn-primary btn-sm" id="add-product-btn"
                                style="min-width: 130px;">
                                <i class="fas fa-plus me-1"></i> Add Product <span id="product-count-badge"
                                    class="badge bg-light text-dark ms-1"></span>
                            </button>
                        </div>
                        <div id="product-container" class="mt-3"></div>
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
                <h5 class="product-heading mb-0"></h5>
                <button type="button" class="btn-close delete-btn"></button>
            </div>
            <div class="row g-3">
                <div class="col-md-6 col-lg-4">
                    <label class="form-label">Product</label>
                    <input type="text" class="form-control product-name" name="products[][product_name]" required />
                </div>
                <div class="col-md-6 col-lg-4">
                    <label class="form-label">Brand Name</label>
                    <input type="text" class="form-control brand-name" name="products[][brand_name]" />
                </div>
                <div class="col-md-6 col-lg-4">
                    <label class="form-label">Number of Bags</label>
                    <input type="number" class="form-control num-bags" name="products[][num_bags]" step="1" required />
                </div>
                <div class="col-md-6 col-lg-4">
                    <label class="form-label">Size of Bag (kg)</label>
                    <input type="number" class="form-control bag-size" name="products[][bag_size]" step="0.01" required />
                </div>
                <div class="col-md-6 col-lg-4">
                    <label class="form-label">Total Kg</label>
                    <input type="number" class="form-control total-kg" name="products[][total_kg]" step="0.01" required />
                </div>
                <div class="col-md-6 col-lg-4">
                    <label class="form-label">Rate</label>
                    <input type="number" class="form-control product-rate" name="products[][rate]" step="0.01" required />
                </div>
                <div class="col-md-6 col-lg-4">
                    <label class="form-label">Total Amount of Product</label>
                    <input type="number" class="form-control product-amount" name="products[][product_amount]" step="0.01"
                        value="0.00" required />
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
            const productCountBadge = document.getElementById('product-count-badge');

            // Set initial state for payment section
            paymentDetailsSection.style.display = paidSwitch.checked ? "block" : "none";

            // Toggle payment details section
            paidSwitch.addEventListener("change", () => {
                paymentDetailsSection.style.display = paidSwitch.checked ? "block" : "none";
            });

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
                    item.querySelector(".product-heading").textContent = `Product ${index + 1}`;
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