@extends('layouts.app')

@section('content')
    <div class="page-content container-fluid">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h3 class="mb-0 text-center" style="color: #ff6347; font-weight: bold">
                    <i class="fas fa-file-invoice me-2"></i>Upload Invoice (AI)
                </h3>
                <p class="text-muted text-center small mb-0 mt-2">PaddleOCR extracts text; Ollama structures it. Review before
                    saving — nothing is stored until you confirm.</p>
            </div>
            <div class="card-body">
                <div class="row g-3 align-items-end mb-4">
                    <div class="col-md-8">
                        <label for="invoiceImage" class="form-label">Invoice image</label>
                        <input type="file" class="form-control" id="invoiceImage" accept="image/*">
                    </div>
                    <div class="col-md-4 d-grid">
                        <button type="button" class="btn text-white" id="btnScan"
                            style="background-color: #ff6347; border: none;">
                            <span class="scan-label"><i class="fas fa-wand-magic-sparkles me-2"></i>Scan Invoice</span>
                            <span class="scan-spinner d-none spinner-border spinner-border-sm" role="status"
                                aria-hidden="true"></span>
                        </button>
                    </div>
                </div>

                <div id="alertArea"></div>

                <div id="previewSection" class="d-none">
                    <hr>
                    <h5 class="mb-3">Review &amp; edit</h5>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Party name <span class="text-muted small">(seller who issued the bill)</span></label>
                            <input type="text" class="form-control" id="fieldParty" placeholder="e.g. supplier / letterhead name, not your shop">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bill no.</label>
                            <input type="text" class="form-control" id="fieldBillNo">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date</label>
                            <input type="text" class="form-control" id="fieldDate" placeholder="e.g. 2025-01-15">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Total amount</label>
                            <input type="number" step="0.01" class="form-control" id="fieldTotal">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Products</h6>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnAddRow">
                            <i class="fas fa-plus me-1"></i>Add row
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="itemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 120px;">Brand</th>
                                    <th>Product name</th>
                                    <th style="width: 90px;">Bags</th>
                                    <th style="width: 110px;">Qty (kg)</th>
                                    <th style="width: 100px;">Rate/kg</th>
                                    <th style="width: 110px;">Amount</th>
                                    <th style="width: 56px;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody"></tbody>
                        </table>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <button type="button" class="btn btn-success px-4" id="btnConfirm">
                            <span class="confirm-label"><i class="fas fa-check me-2"></i>Confirm &amp; save</span>
                            <span class="confirm-spinner d-none spinner-border spinner-border-sm" role="status"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let extractedData = null;

        const alertArea = document.getElementById('alertArea');
        const previewSection = document.getElementById('previewSection');
        const itemsBody = document.getElementById('itemsBody');

        function showAlert(type, message) {
            alertArea.innerHTML =
                `<div class="alert alert-${type} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
        }

        function clearAlerts() {
            alertArea.innerHTML = '';
        }

        function itemRowHtml(item, index) {
            const brand = item?.brand ?? '';
            const name = item?.name ?? '';
            const bags = item?.bags ?? '';
            const qty = item?.quantity ?? '';
            const rate = item?.rate ?? '';
            const amount = item?.amount ?? '';
            return `
            <tr data-row-index="${index}">
                <td><input type="text" class="form-control form-control-sm item-brand" value="${escapeAttr(brand)}" placeholder="Brand"></td>
                <td><input type="text" class="form-control form-control-sm item-name" value="${escapeAttr(name)}"></td>
                <td><input type="number" step="1" class="form-control form-control-sm item-bags" value="${escapeAttr(bags)}"></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm item-qty" value="${escapeAttr(qty)}"></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm item-rate" value="${escapeAttr(rate)}"></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm item-amount" value="${escapeAttr(amount)}"></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" title="Remove"><i class="fas fa-times"></i></button></td>
            </tr>`;
        }

        function escapeAttr(v) {
            return String(v).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
        }

        function renderItems(items) {
            itemsBody.innerHTML = '';
            (items || []).forEach((item, i) => {
                itemsBody.insertAdjacentHTML('beforeend', itemRowHtml(item, i));
            });
            bindRemoveButtons();
        }

        function bindRemoveButtons() {
            itemsBody.querySelectorAll('.btn-remove-row').forEach(btn => {
                btn.onclick = () => {
                    btn.closest('tr').remove();
                    if (!itemsBody.querySelector('tr')) {
                        renderItems([{
                            brand: '',
                            name: '',
                            bags: 0,
                            quantity: 0,
                            rate: 0,
                            amount: 0
                        }]);
                    }
                };
            });
        }

        function readFormIntoPayload() {
            const rows = itemsBody.querySelectorAll('tr');
            const items = [];
            rows.forEach(tr => {
                items.push({
                    brand: tr.querySelector('.item-brand').value.trim(),
                    name: tr.querySelector('.item-name').value.trim(),
                    bags: parseFloat(tr.querySelector('.item-bags').value) || 0,
                    quantity: parseFloat(tr.querySelector('.item-qty').value) || 0,
                    rate: parseFloat(tr.querySelector('.item-rate').value) || 0,
                    amount: parseFloat(tr.querySelector('.item-amount').value) || 0,
                });
            });
            return {
                party_name: document.getElementById('fieldParty').value.trim(),
                bill_no: document.getElementById('fieldBillNo').value.trim(),
                date: document.getElementById('fieldDate').value.trim(),
                total: parseFloat(document.getElementById('fieldTotal').value) || 0,
                items
            };
        }

        function fillPreview(data) {
            document.getElementById('fieldParty').value = data.party_name || '';
            document.getElementById('fieldBillNo').value = data.bill_no || '';
            document.getElementById('fieldDate').value = data.date || '';
            document.getElementById('fieldTotal').value = data.total != null ? data.total : '';
            renderItems(data.items || []);
            previewSection.classList.remove('d-none');
        }

        document.getElementById('btnAddRow').onclick = () => {
            itemsBody.insertAdjacentHTML('beforeend', itemRowHtml({}, itemsBody.querySelectorAll('tr').length));
            bindRemoveButtons();
        };

        document.getElementById('btnScan').onclick = async () => {
            const fileInput = document.getElementById('invoiceImage');
            if (!fileInput.files || !fileInput.files[0]) {
                showAlert('warning', 'Choose an invoice image first.');
                return;
            }
            clearAlerts();
            const btn = document.getElementById('btnScan');
            btn.disabled = true;
            btn.querySelector('.scan-label').classList.add('d-none');
            btn.querySelector('.scan-spinner').classList.remove('d-none');

            const formData = new FormData();
            formData.append('image', fileInput.files[0]);

            try {
                const res = await fetch(@json(route('ai.invoice.process')), {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                });
                const json = await res.json();
                if (!res.ok) {
                    const msg = json.message || json.error || 'Scan failed.';
                    const hint = json.hint ? '<br><strong>What to do:</strong> ' + json.hint : '';
                    const extra = json.errors ? '<br><small>' + JSON.stringify(json.errors) + '</small>' : '';
                    showAlert('danger', msg + hint + extra);
                    return;
                }
                extractedData = json.data;
                fillPreview(extractedData);
                showAlert('success', 'Text extracted and structured. Review the fields below, then confirm to save.');
            } catch (e) {
                showAlert('danger', 'Network error. Check your connection and try again.');
            } finally {
                btn.disabled = false;
                btn.querySelector('.scan-label').classList.remove('d-none');
                btn.querySelector('.scan-spinner').classList.add('d-none');
            }
        };

        document.getElementById('btnConfirm').onclick = async () => {
            clearAlerts();
            const payload = readFormIntoPayload();
            if (!payload.bill_no || !payload.party_name || !payload.date) {
                showAlert('warning', 'Bill number, party name, and date are required.');
                return;
            }
            const namedItems = payload.items.filter(i => i.name.length > 0);
            if (namedItems.length === 0) {
                showAlert('warning', 'Add at least one product with a name.');
                return;
            }
            payload.items = namedItems;

            const btn = document.getElementById('btnConfirm');
            btn.disabled = true;
            btn.querySelector('.confirm-label').classList.add('d-none');
            btn.querySelector('.confirm-spinner').classList.remove('d-none');

            try {
                const res = await fetch(@json(route('ai.invoice.save')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });
                const json = await res.json();
                if (!res.ok) {
                    let msg = json.message || 'Save failed.';
                    if (json.errors) {
                        msg += ' ' + JSON.stringify(json.errors);
                    }
                    showAlert('danger', msg);
                    return;
                }
                if (typeof Swal !== 'undefined') {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Saved',
                        text: 'Bill was added to Vashi Market.',
                    });
                } else {
                    alert('Saved successfully.');
                }
                if (json.redirect) {
                    window.location.href = json.redirect;
                }
            } catch (e) {
                showAlert('danger', 'Network error while saving.');
            } finally {
                btn.disabled = false;
                btn.querySelector('.confirm-label').classList.remove('d-none');
                btn.querySelector('.confirm-spinner').classList.add('d-none');
            }
        };
    </script>
@endsection
