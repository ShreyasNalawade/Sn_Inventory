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

        .bill-list-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.85rem;
        }

        .select-all-btn,
        .bill-select-toggle {
            border: 0;
            background: transparent;
            padding: 0;
            text-align: left;
        }

        .select-all-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            min-height: 44px;
            padding: 0.45rem 0.75rem;
            border-radius: 999px;
            background: #fff7ed;
            color: #9a3412;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .bill-pick-list {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }

        .bill-row {
            border: 1.5px solid #e5e7eb;
            border-radius: 1rem;
            background: #fff;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
            transition: border-color 0.2s, box-shadow 0.2s, background-color 0.2s;
        }

        .bill-row.is-selected {
            border-color: #16a34a;
            background: #f0fdf4;
            box-shadow: 0 6px 18px rgba(22, 163, 74, 0.12);
        }

        .bill-select-toggle {
            display: flex;
            align-items: flex-start;
            gap: 0.8rem;
            width: 100%;
            min-height: 72px;
            padding: 0.95rem 1rem;
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
        }

        .bill-check-ui {
            flex: 0 0 28px;
            width: 28px;
            height: 28px;
            margin-top: 0.1rem;
            border: 2px solid #94a3b8;
            border-radius: 0.55rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            color: transparent;
            font-size: 0.85rem;
        }

        .bill-row.is-selected .bill-check-ui {
            border-color: #16a34a;
            background: #16a34a;
            color: #fff;
        }

        .bill-checkbox {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .bill-select-main {
            flex: 1;
            min-width: 0;
        }

        .bill-select-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.45rem;
        }

        .bill-no-text {
            display: block;
            font-size: 1.05rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
            word-break: break-word;
        }

        .bill-date-text {
            display: block;
            margin-top: 0.2rem;
            font-size: 0.82rem;
            color: #64748b;
        }

        .bill-amount-chip {
            flex-shrink: 0;
            text-align: right;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.7rem;
            padding: 0.45rem 0.65rem;
        }

        .bill-row.is-selected .bill-amount-chip {
            background: #fff;
            border-color: #bbf7d0;
        }

        .bill-amount-chip .chip-label {
            display: block;
            font-size: 0.68rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .bill-amount-chip .chip-value {
            display: block;
            margin-top: 0.1rem;
            font-size: 0.98rem;
            font-weight: 800;
            color: #0f172a;
        }

        .bill-products-text {
            font-size: 0.88rem;
            color: #334155;
            line-height: 1.35;
            word-break: break-word;
        }

        .bill-tap-hint {
            display: block;
            margin-top: 0.45rem;
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 600;
        }

        .bill-row.is-selected .bill-tap-hint {
            color: #15803d;
        }

        .bill-allocation-panel {
            display: none;
            padding: 0 1rem 1rem;
            border-top: 1px solid #dcfce7;
            background: rgba(255, 255, 255, 0.72);
        }

        .bill-row.is-selected .bill-allocation-panel {
            display: block;
        }

        .bill-allocation-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.65rem;
            padding-top: 0.85rem;
        }

        .bill-allocation-field label {
            display: block;
            margin-bottom: 0.3rem;
            font-size: 0.72rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-weight: 700;
        }

        .bill-allocation-field .form-control {
            min-height: 44px;
            font-size: 1rem;
            font-weight: 700;
        }

        .bill-percent-box {
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.65rem 0.75rem;
            border-radius: 0.7rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .bill-percent-box .percent-label {
            font-size: 0.78rem;
            color: #64748b;
            font-weight: 700;
            text-transform: uppercase;
        }

        .bill-percent-box .percent-text {
            font-size: 0.95rem;
            font-weight: 800;
            color: #0f172a;
        }

        @media (min-width: 768px) {
            .bill-pick-list {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1rem;
            }
        }

        @media (max-width: 575.98px) {
            .page-content .card-body {
                padding: 0.9rem;
            }

            .pay-summary-card {
                padding: 0.8rem;
            }

            .bill-select-toggle {
                padding: 0.9rem;
            }

            .bill-allocation-panel {
                padding: 0 0.9rem 0.9rem;
            }

            .save-payment-wrap {
                position: sticky;
                bottom: 0.75rem;
                z-index: 5;
            }

            .save-payment-wrap .btn {
                min-height: 48px;
                box-shadow: 0 8px 20px rgba(37, 99, 235, 0.28);
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

                        <div class="mb-4">
                            <div class="bill-list-toolbar">
                                <h5 class="mb-0">Select Bills</h5>
                                <button type="button" class="select-all-btn" id="select-all-bills">
                                    <i class="fas fa-check-double"></i>
                                    <span id="select-all-label">Select All</span>
                                </button>
                            </div>

                            <div class="bill-pick-list">
                                @foreach ($bills as $index => $bill)
                                    @php
                                        $products = $bill->products->pluck('product_name')->implode(', ');
                                    @endphp
                                    <div class="bill-row" data-bill-id="{{ $bill->id }}"
                                        data-bill-amount="{{ (float) $bill->total_bill_amount }}">
                                        <input type="checkbox" class="bill-checkbox" value="{{ $bill->id }}" tabindex="-1">
                                        <input type="hidden" class="bill-id-input" disabled
                                            name="bills[{{ $index }}][id]" value="{{ $bill->id }}">

                                        <button type="button" class="bill-select-toggle" aria-pressed="false">
                                            <span class="bill-check-ui" aria-hidden="true">
                                                <i class="fas fa-check"></i>
                                            </span>
                                            <span class="bill-select-main">
                                                <span class="bill-select-top">
                                                    <span>
                                                        <span class="bill-no-text">Bill #{{ $bill->bill_no }}</span>
                                                        <span class="bill-date-text">
                                                            <i class="fas fa-calendar-alt me-1"></i>
                                                            {{ \Carbon\Carbon::parse($bill->bill_date)->format('d/m/Y') }}
                                                        </span>
                                                    </span>
                                                    <span class="bill-amount-chip">
                                                        <span class="chip-label">Bill Amount</span>
                                                        <span class="chip-value">₹{{ number_format((float) $bill->total_bill_amount, 2) }}</span>
                                                    </span>
                                                </span>
                                                <span class="bill-products-text">
                                                    <i class="fas fa-box me-1 text-muted"></i>
                                                    {{ $products ?: 'No products' }}
                                                </span>
                                                <span class="bill-tap-hint">Tap card to select</span>
                                            </span>
                                        </button>

                                        <div class="bill-allocation-panel">
                                            <div class="bill-allocation-grid">
                                                <div class="bill-allocation-field">
                                                    <label>Paid Share</label>
                                                    <input type="number" step="0.01" min="0"
                                                        class="form-control allocated-input" disabled
                                                        name="bills[{{ $index }}][allocated_amount]" value="0.00">
                                                </div>
                                                <div class="bill-allocation-field">
                                                    <label>Interest / Offer</label>
                                                    <input type="number" step="0.01"
                                                        class="form-control interest-input" disabled
                                                        name="bills[{{ $index }}][interest_difference]" value="0.00">
                                                </div>
                                                <div class="bill-percent-box">
                                                    <span class="percent-label">Percentage</span>
                                                    <span class="percent-text">0.00%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="d-grid save-payment-wrap">
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
                const toggleBtn = row.querySelector('.bill-select-toggle');
                const hint = row.querySelector('.bill-tap-hint');

                checkbox.checked = enabled;
                idInput.disabled = !enabled;
                allocatedInput.disabled = !enabled;
                interestInput.disabled = !enabled;

                if (!enabled) {
                    allocatedInput.value = '0.00';
                    interestInput.value = '0.00';
                    row.querySelector('.percent-text').textContent = '0.00%';
                    interestInput.classList.remove('positive', 'negative');
                }

                row.classList.toggle('is-selected', enabled);
                toggleBtn.setAttribute('aria-pressed', enabled ? 'true' : 'false');
                if (hint) {
                    hint.textContent = enabled ? 'Selected · tap again to unselect' : 'Tap card to select';
                }
            };

            const updateSelectAllLabel = () => {
                const allRows = document.querySelectorAll('.bill-row');
                const selectedCount = getSelectedRows().length;
                const label = document.getElementById('select-all-label');
                if (!label) {
                    return;
                }

                if (selectedCount > 0 && selectedCount === allRows.length) {
                    label.textContent = 'Unselect All';
                } else {
                    label.textContent = 'Select All';
                }
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
                updateSelectAllLabel();
            };

            document.querySelectorAll('.bill-select-toggle').forEach((toggleBtn) => {
                toggleBtn.addEventListener('click', () => {
                    const row = toggleBtn.closest('.bill-row');
                    const checkbox = row.querySelector('.bill-checkbox');
                    setRowEnabled(row, !checkbox.checked);
                    applyProportionalSplit();
                });
            });

            selectAll?.addEventListener('click', () => {
                const allRows = Array.from(document.querySelectorAll('.bill-row'));
                const shouldSelectAll = getSelectedRows().length !== allRows.length;

                allRows.forEach((row) => {
                    setRowEnabled(row, shouldSelectAll);
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
