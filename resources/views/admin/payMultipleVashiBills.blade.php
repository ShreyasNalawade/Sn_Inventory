@extends('layouts.app')

@section('styles')
    <style>
        .pay-summary-card {
            border: 1px solid #e2e8f0;
            border-radius: 0.85rem;
            background: #f8fafc;
            padding: 1rem;
        }

        .pay-summary-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }

        @media (min-width: 768px) {
            .pay-summary-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        .pay-summary-item {
            background: #fff;
            border: 1px solid #eef2f7;
            border-radius: 0.7rem;
            padding: 0.7rem 0.8rem;
        }

        .pay-summary-item .label {
            display: block;
            font-size: 0.72rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .pay-summary-item .value {
            display: block;
            margin-top: 0.2rem;
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
        }

        .pay-summary-item .value.positive {
            color: #b91c1c;
        }

        .pay-summary-item .value.negative {
            color: #166534;
        }

        .bill-select-card {
            border: 1px solid #e5e7eb;
            border-radius: 0.85rem;
            overflow: hidden;
        }

        .bill-select-table th {
            white-space: nowrap;
            font-size: 0.82rem;
        }

        .bill-select-table td {
            vertical-align: middle;
            font-size: 0.9rem;
        }

        .interest-input.positive {
            color: #b91c1c;
            font-weight: 600;
        }

        .interest-input.negative {
            color: #166534;
            font-weight: 600;
        }

        .allocation-mismatch {
            color: #b91c1c;
            font-weight: 600;
        }

        .allocation-ok {
            color: #166534;
            font-weight: 600;
        }

        @media (max-width: 767.98px) {
            .bill-select-table thead {
                display: none;
            }

            .bill-select-table,
            .bill-select-table tbody,
            .bill-select-table tr,
            .bill-select-table td {
                display: block;
                width: 100%;
            }

            .bill-select-table tr {
                border-bottom: 1px solid #e5e7eb;
                padding: 0.85rem 0.75rem;
            }

            .bill-select-table td {
                border: 0;
                padding: 0.35rem 0;
            }

            .bill-select-table td::before {
                content: attr(data-label);
                display: block;
                font-size: 0.72rem;
                color: #64748b;
                text-transform: uppercase;
                margin-bottom: 0.15rem;
            }

            .bill-select-table td.col-check::before {
                display: none;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-content container-fluid">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h3 class="card-title fw-bold text-dark mb-0">Pay Multiple Bills</h3>
                <a href="{{ route('vashi-market.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>

            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="alert alert-info">
                    Select unpaid bills of the same party, enter one cheque/UPI amount, and split interest per bill.
                    Existing paid bills and old data are not deleted.
                </div>

                <form id="party-filter-form" method="GET" action="{{ route('vashi-market.payments.create') }}" class="mb-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label for="party_name" class="form-label">Party Name</label>
                            <select name="party_name" id="party_name" class="form-select" required>
                                <option value="">Select unpaid party</option>
                                @foreach ($parties as $party)
                                    <option value="{{ $party }}" @selected($partyName === $party)>{{ $party }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-info w-100">
                                <i class="fas fa-search me-1"></i> Load Unpaid Bills
                            </button>
                        </div>
                    </div>
                </form>

                @if ($partyName === '')
                    <div class="text-muted text-center py-4">Select a party to load unpaid bills.</div>
                @elseif ($bills->isEmpty())
                    <div class="text-muted text-center py-4">No unpaid bills found for this party.</div>
                @else
                    <form id="group-payment-form" method="POST" action="{{ route('vashi-market.payments.store') }}">
                        @csrf
                        <input type="hidden" name="party_name" value="{{ $partyName }}">

                        <div class="form-section mb-4">
                            <h5 class="mb-3">Payment Details (entered once)</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="payment_type" class="form-label">Type of Payment</label>
                                    <select class="form-select" id="payment_type" name="payment_type" required>
                                        <option value="">Select Payment Type</option>
                                        <option value="Gpay" @selected(old('payment_type') === 'Gpay')>Gpay</option>
                                        <option value="Cheque" @selected(old('payment_type') === 'Cheque')>Cheque</option>
                                        <option value="Cash" @selected(old('payment_type') === 'Cash')>Cash</option>
                                        <option value="Account Transfer" @selected(old('payment_type') === 'Account Transfer')>Account Transfer</option>
                                    </select>
                                </div>
                                <div class="col-md-4" id="transaction-id-wrap" style="display:none;">
                                    <label for="transaction_id" class="form-label">Transaction ID</label>
                                    <input type="text" class="form-control" id="transaction_id" name="transaction_id"
                                        value="{{ old('transaction_id') }}">
                                </div>
                                <div class="col-md-4" id="cheque-no-wrap" style="display:none;">
                                    <label for="cheque_no" class="form-label">Cheque No</label>
                                    <input type="text" class="form-control" id="cheque_no" name="cheque_no"
                                        value="{{ old('cheque_no') }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="receipt_no" class="form-label">Receipt Number</label>
                                    <input type="text" class="form-control" id="receipt_no" name="receipt_no"
                                        value="{{ old('receipt_no') }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="paid_date" class="form-label">Paid Date</label>
                                    <input type="date" class="form-control" id="paid_date" name="paid_date"
                                        value="{{ old('paid_date', now()->toDateString()) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="total_paid_amount" class="form-label">Total Paid Amount</label>
                                    <input type="number" step="0.01" min="0.01" class="form-control"
                                        id="total_paid_amount" name="total_paid_amount"
                                        value="{{ old('total_paid_amount') }}" required>
                                </div>
                                <div class="col-12">
                                    <label for="notes" class="form-label">Notes (optional)</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="pay-summary-card mb-4">
                            <div class="pay-summary-grid">
                                <div class="pay-summary-item">
                                    <span class="label">Selected Bills</span>
                                    <span class="value" id="summary-count">0</span>
                                </div>
                                <div class="pay-summary-item">
                                    <span class="label">Bills Total</span>
                                    <span class="value" id="summary-bills-total">₹0.00</span>
                                </div>
                                <div class="pay-summary-item">
                                    <span class="label">Paid Total</span>
                                    <span class="value" id="summary-paid-total">₹0.00</span>
                                </div>
                                <div class="pay-summary-item">
                                    <span class="label">Interest / Offer</span>
                                    <span class="value" id="summary-difference">₹0.00</span>
                                </div>
                            </div>
                            <div class="mt-3 small" id="allocation-status" class="allocation-ok">
                                Select bills and enter paid amount to auto-split.
                            </div>
                        </div>

                        <div class="bill-select-card mb-4">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 bill-select-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>
                                                <input type="checkbox" id="select-all-bills" class="form-check-input">
                                            </th>
                                            <th>Bill No</th>
                                            <th>Date</th>
                                            <th>Products</th>
                                            <th>Bill Amount</th>
                                            <th>Paid Share</th>
                                            <th>Interest / Offer</th>
                                            <th>%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($bills as $index => $bill)
                                            @php
                                                $products = $bill->products->pluck('product_name')->implode(', ');
                                            @endphp
                                            <tr class="bill-row" data-bill-id="{{ $bill->id }}"
                                                data-bill-amount="{{ (float) $bill->total_bill_amount }}">
                                                <td class="col-check" data-label="Select">
                                                    <input type="checkbox" class="form-check-input bill-checkbox"
                                                        value="{{ $bill->id }}">
                                                    <input type="hidden" class="bill-id-input" disabled
                                                        name="bills[{{ $index }}][id]" value="{{ $bill->id }}">
                                                </td>
                                                <td data-label="Bill No"><strong>{{ $bill->bill_no }}</strong></td>
                                                <td data-label="Date">
                                                    {{ \Carbon\Carbon::parse($bill->bill_date)->format('d/m/Y') }}
                                                </td>
                                                <td data-label="Products">{{ $products ?: '-' }}</td>
                                                <td data-label="Bill Amount">
                                                    ₹{{ number_format((float) $bill->total_bill_amount, 2) }}
                                                </td>
                                                <td data-label="Paid Share">
                                                    <input type="number" step="0.01" min="0"
                                                        class="form-control form-control-sm allocated-input" disabled
                                                        name="bills[{{ $index }}][allocated_amount]" value="0.00">
                                                </td>
                                                <td data-label="Interest / Offer">
                                                    <input type="number" step="0.01"
                                                        class="form-control form-control-sm interest-input" disabled
                                                        name="bills[{{ $index }}][interest_difference]" value="0.00">
                                                </td>
                                                <td data-label="%">
                                                    <span class="percent-text">0.00%</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="d-grid d-md-flex justify-content-md-end gap-2">
                            <button type="submit" id="save-payment-btn" class="btn btn-primary" disabled>
                                <i class="fas fa-save me-1"></i> Save Group Payment
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const paymentType = document.getElementById('payment_type');
            const transactionWrap = document.getElementById('transaction-id-wrap');
            const chequeWrap = document.getElementById('cheque-no-wrap');
            const totalPaidInput = document.getElementById('total_paid_amount');
            const selectAll = document.getElementById('select-all-bills');
            const saveBtn = document.getElementById('save-payment-btn');
            const form = document.getElementById('group-payment-form');

            if (!form) {
                return;
            }

            const formatMoney = (amount) => `₹${Number(amount || 0).toFixed(2)}`;

            const updatePaymentTypeFields = () => {
                const value = paymentType.value;
                transactionWrap.style.display = (value === 'Gpay' || value === 'Account Transfer') ? 'block' : 'none';
                chequeWrap.style.display = value === 'Cheque' ? 'block' : 'none';
            };

            const getSelectedRows = () => {
                return Array.from(document.querySelectorAll('.bill-row')).filter((row) => {
                    return row.querySelector('.bill-checkbox').checked;
                });
            };

            const setRowEnabled = (row, enabled) => {
                const checkbox = row.querySelector('.bill-checkbox');
                const idInput = row.querySelector('.bill-id-input');
                const allocatedInput = row.querySelector('.allocated-input');
                const interestInput = row.querySelector('.interest-input');

                idInput.disabled = !enabled;
                allocatedInput.disabled = !enabled;
                interestInput.disabled = !enabled;

                if (!enabled) {
                    allocatedInput.value = '0.00';
                    interestInput.value = '0.00';
                    row.querySelector('.percent-text').textContent = '0.00%';
                    interestInput.classList.remove('positive', 'negative');
                }

                checkbox.closest('tr').classList.toggle('table-active', enabled);
            };

            const updateInterestStyle = (interestInput, interest) => {
                interestInput.classList.remove('positive', 'negative');
                if (interest > 0) {
                    interestInput.classList.add('positive');
                } else if (interest < 0) {
                    interestInput.classList.add('negative');
                }
            };

            const applyProportionalSplit = () => {
                const selectedRows = getSelectedRows();
                const totalPaid = parseFloat(totalPaidInput.value) || 0;
                const billsTotal = selectedRows.reduce((sum, row) => sum + (parseFloat(row.dataset.billAmount) || 0), 0);

                if (!selectedRows.length || billsTotal <= 0 || totalPaid <= 0) {
                    selectedRows.forEach((row) => {
                        row.querySelector('.allocated-input').value = '0.00';
                        row.querySelector('.interest-input').value = '0.00';
                        row.querySelector('.percent-text').textContent = '0.00%';
                    });
                    refreshSummary();
                    return;
                }

                let allocatedRunning = 0;

                selectedRows.forEach((row, index) => {
                    const billAmount = parseFloat(row.dataset.billAmount) || 0;
                    let allocated;

                    if (index === selectedRows.length - 1) {
                        allocated = Math.round((totalPaid - allocatedRunning) * 100) / 100;
                    } else {
                        allocated = Math.round(((billAmount / billsTotal) * totalPaid) * 100) / 100;
                        allocatedRunning += allocated;
                    }

                    const interest = Math.round((allocated - billAmount) * 100) / 100;
                    const percent = billAmount > 0 ? Math.abs(interest) / billAmount * 100 : 0;

                    row.querySelector('.allocated-input').value = allocated.toFixed(2);
                    row.querySelector('.interest-input').value = interest.toFixed(2);
                    row.querySelector('.percent-text').textContent = `${percent.toFixed(2)}%`;
                    updateInterestStyle(row.querySelector('.interest-input'), interest);
                });

                refreshSummary();
            };

            const refreshSummary = () => {
                const selectedRows = getSelectedRows();
                const billsTotal = selectedRows.reduce((sum, row) => sum + (parseFloat(row.dataset.billAmount) || 0), 0);
                const allocatedTotal = selectedRows.reduce((sum, row) => {
                    return sum + (parseFloat(row.querySelector('.allocated-input').value) || 0);
                }, 0);
                const totalPaid = parseFloat(totalPaidInput.value) || 0;
                const difference = allocatedTotal - billsTotal;
                const remaining = Math.round((totalPaid - allocatedTotal) * 100) / 100;

                document.getElementById('summary-count').textContent = String(selectedRows.length);
                document.getElementById('summary-bills-total').textContent = formatMoney(billsTotal);
                document.getElementById('summary-paid-total').textContent = formatMoney(totalPaid);

                const differenceEl = document.getElementById('summary-difference');
                if (difference > 0) {
                    differenceEl.textContent = `+₹${Math.abs(difference).toFixed(2)}`;
                    differenceEl.className = 'value positive';
                } else if (difference < 0) {
                    differenceEl.textContent = `-₹${Math.abs(difference).toFixed(2)}`;
                    differenceEl.className = 'value negative';
                } else {
                    differenceEl.textContent = '₹0.00';
                    differenceEl.className = 'value';
                }

                const statusEl = document.getElementById('allocation-status');
                const canSave = selectedRows.length > 0 && totalPaid > 0 && Math.abs(remaining) <= 0.05;

                if (!selectedRows.length) {
                    statusEl.textContent = 'Select at least one unpaid bill.';
                    statusEl.className = 'mt-3 small allocation-mismatch';
                } else if (totalPaid <= 0) {
                    statusEl.textContent = 'Enter the total paid amount for the cheque/UPI.';
                    statusEl.className = 'mt-3 small allocation-mismatch';
                } else if (Math.abs(remaining) > 0.05) {
                    statusEl.textContent = `Allocated mismatch. Remaining to match paid total: ₹${remaining.toFixed(2)}`;
                    statusEl.className = 'mt-3 small allocation-mismatch';
                } else {
                    statusEl.textContent = 'Allocation matches paid total. You can edit interest per bill before saving.';
                    statusEl.className = 'mt-3 small allocation-ok';
                }

                saveBtn.disabled = !canSave;
            };

            document.querySelectorAll('.bill-checkbox').forEach((checkbox) => {
                checkbox.addEventListener('change', () => {
                    const row = checkbox.closest('.bill-row');
                    setRowEnabled(row, checkbox.checked);
                    applyProportionalSplit();
                });
            });

            selectAll?.addEventListener('change', () => {
                document.querySelectorAll('.bill-checkbox').forEach((checkbox) => {
                    checkbox.checked = selectAll.checked;
                    setRowEnabled(checkbox.closest('.bill-row'), selectAll.checked);
                });
                applyProportionalSplit();
            });

            totalPaidInput?.addEventListener('input', applyProportionalSplit);
            paymentType?.addEventListener('change', updatePaymentTypeFields);

            document.querySelectorAll('.allocated-input').forEach((input) => {
                input.addEventListener('input', () => {
                    const row = input.closest('.bill-row');
                    const billAmount = parseFloat(row.dataset.billAmount) || 0;
                    const allocated = parseFloat(input.value) || 0;
                    const interest = Math.round((allocated - billAmount) * 100) / 100;
                    const percent = billAmount > 0 ? Math.abs(interest) / billAmount * 100 : 0;

                    row.querySelector('.interest-input').value = interest.toFixed(2);
                    row.querySelector('.percent-text').textContent = `${percent.toFixed(2)}%`;
                    updateInterestStyle(row.querySelector('.interest-input'), interest);
                    refreshSummary();
                });
            });

            document.querySelectorAll('.interest-input').forEach((input) => {
                input.addEventListener('input', () => {
                    const row = input.closest('.bill-row');
                    const billAmount = parseFloat(row.dataset.billAmount) || 0;
                    const interest = parseFloat(input.value) || 0;
                    const allocated = Math.round((billAmount + interest) * 100) / 100;
                    const percent = billAmount > 0 ? Math.abs(interest) / billAmount * 100 : 0;
                    const totalPaid = parseFloat(totalPaidInput.value) || 0;

                    row.querySelector('.allocated-input').value = allocated.toFixed(2);
                    row.querySelector('.percent-text').textContent = `${percent.toFixed(2)}%`;
                    updateInterestStyle(input, interest);

                    // Keep total paid fixed: redistribute remaining amount across other selected bills.
                    const selectedRows = getSelectedRows();
                    const otherRows = selectedRows.filter((item) => item !== row);

                    if (otherRows.length && totalPaid > 0) {
                        const remainingPaid = Math.round((totalPaid - allocated) * 100) / 100;
                        const otherBillsTotal = otherRows.reduce((sum, item) => {
                            return sum + (parseFloat(item.dataset.billAmount) || 0);
                        }, 0);

                        let running = 0;
                        otherRows.forEach((otherRow, index) => {
                            const otherBillAmount = parseFloat(otherRow.dataset.billAmount) || 0;
                            let otherAllocated;

                            if (index === otherRows.length - 1) {
                                otherAllocated = Math.round((remainingPaid - running) * 100) / 100;
                            } else if (otherBillsTotal > 0) {
                                otherAllocated = Math.round(((otherBillAmount / otherBillsTotal) * remainingPaid) * 100) / 100;
                                running += otherAllocated;
                            } else {
                                otherAllocated = 0;
                            }

                            const otherInterest = Math.round((otherAllocated - otherBillAmount) * 100) / 100;
                            const otherPercent = otherBillAmount > 0
                                ? Math.abs(otherInterest) / otherBillAmount * 100
                                : 0;

                            otherRow.querySelector('.allocated-input').value = otherAllocated.toFixed(2);
                            otherRow.querySelector('.interest-input').value = otherInterest.toFixed(2);
                            otherRow.querySelector('.percent-text').textContent = `${otherPercent.toFixed(2)}%`;
                            updateInterestStyle(otherRow.querySelector('.interest-input'), otherInterest);
                        });
                    }

                    refreshSummary();
                });
            });

            form.addEventListener('submit', (event) => {
                const selectedRows = getSelectedRows();
                const totalPaid = parseFloat(totalPaidInput.value) || 0;
                const allocatedTotal = selectedRows.reduce((sum, row) => {
                    return sum + (parseFloat(row.querySelector('.allocated-input').value) || 0);
                }, 0);

                if (!selectedRows.length) {
                    event.preventDefault();
                    alert('Select at least one bill.');
                    return;
                }

                if (Math.abs(allocatedTotal - totalPaid) > 0.05) {
                    event.preventDefault();
                    alert('Sum of paid shares must equal the total paid amount.');
                }
            });

            updatePaymentTypeFields();
            refreshSummary();
        });
    </script>
@endsection
