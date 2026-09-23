@extends('layouts.app')

@section('styles')
    <style>
        .data-card-link {
            text-decoration: none !important;
            color: inherit;
        }

        .data-card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .data-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .bill-card {
            border: 1px solid #e5e7eb;
            border-left-width: 4px;
            overflow: hidden;
        }

        .bill-card-paid {
            border-left-color: #16a34a;
        }

        .bill-card-unpaid {
            border-left-color: #f59e0b;
        }

        .bill-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.85rem;
        }

        .bill-card-title-wrap {
            min-width: 0;
        }

        .bill-no-label {
            display: block;
            font-size: 0.72rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 0.15rem;
        }

        .bill-no {
            display: block;
            font-size: 1.05rem;
            font-weight: 700;
            color: #111827;
            word-break: break-word;
        }

        .bill-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            flex-shrink: 0;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .bill-status-badge.paid {
            background: #dcfce7;
            color: #166534;
        }

        .bill-status-badge.unpaid {
            background: #fef3c7;
            color: #92400e;
        }

        .bill-card-meta li {
            gap: 0.55rem;
            margin-bottom: 0.55rem;
            font-size: 0.92rem;
            line-height: 1.4;
        }

        .bill-card-meta li:last-child {
            margin-bottom: 0;
        }

        .bill-card-meta strong {
            margin-right: 0.25rem;
        }

        .bill-card-meta .product-name,
        .bill-card-meta .party-name {
            word-break: break-word;
        }

        .bill-card-footer {
            margin-top: 0.95rem;
            padding-top: 0.85rem;
            border-top: 1px solid #eef2f7;
        }

        .bill-amount-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.65rem;
        }

        .bill-amount-cell {
            min-width: 0;
            padding: 0.55rem 0.7rem;
            border-radius: 0.7rem;
            background: #f8fafc;
            border: 1px solid #eef2f7;
        }

        .amount-label {
            display: block;
            font-size: 0.68rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.15rem;
        }

        .amount-value {
            display: block;
            font-size: 0.98rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.25;
            word-break: break-word;
        }

        .amount-value.paid-amount {
            color: #166534;
        }

        .amount-value.pending-amount {
            color: #c2410c;
            font-size: 0.9rem;
        }

        .interest-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-top: 0.55rem;
            padding: 0.55rem 0.75rem;
            border-radius: 0.7rem;
            font-size: 0.82rem;
            font-weight: 700;
            line-height: 1.3;
        }

        .interest-bar-label,
        .interest-bar-value {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            min-width: 0;
        }

        .interest-bar-value {
            justify-content: flex-end;
            text-align: right;
            flex-wrap: wrap;
        }

        .interest-bar-value small {
            font-size: 0.75rem;
            font-weight: 600;
            opacity: 0.85;
        }

        .interest-bar.interest {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }

        .interest-bar.offer {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .interest-bar.even {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
        }

        @media (max-width: 575.98px) {
            .bill-card .card-body {
                padding: 0.9rem;
            }

            .bill-amount-grid {
                gap: 0.5rem;
            }

            .bill-amount-cell {
                padding: 0.5rem 0.6rem;
            }

            .amount-value {
                font-size: 0.92rem;
            }

            .interest-bar {
                padding: 0.5rem 0.65rem;
                font-size: 0.78rem;
            }
        }

        .skeleton-card {
            pointer-events: none;
        }

        .skeleton-row {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            margin-bottom: 0.9rem;
        }

        .skeleton-row:last-child {
            margin-bottom: 0;
        }

        .skeleton-icon {
            flex: 0 0 1.25rem;
            width: 1.25rem;
            height: 1.25rem;
            border-radius: 50%;
        }

        .skeleton-text {
            flex: 1;
            min-width: 0;
        }

        .skeleton-icon,
        .skeleton-label,
        .skeleton-value {
            position: relative;
            overflow: hidden;
            background: #e9ecef;
        }

        .skeleton-label {
            height: 12px;
            width: 24%;
            margin-bottom: 0.45rem;
            border-radius: 4px;
        }

        .skeleton-value {
            height: 12px;
            width: 58%;
            border-radius: 4px;
        }

        .skeleton-row:nth-child(2) .skeleton-value {
            width: 78%;
        }

        .skeleton-row:nth-child(3) .skeleton-value {
            width: 42%;
        }

        .skeleton-row:nth-child(4) .skeleton-value {
            width: 52%;
        }

        .skeleton-icon::after,
        .skeleton-label::after,
        .skeleton-value::after {
            position: absolute;
            inset: 0;
            content: "";
            transform: translateX(-100%);
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.65), transparent);
            animation: skeleton-shimmer 1.3s infinite;
        }

        @keyframes skeleton-shimmer {
            100% {
                transform: translateX(100%);
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-content container-fluid">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h3 class="card-title text-center fw-bold text-dark">Vashi Market Bill Details</h3>
            </div>
            {{-- Session message display area --}}
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('vashi-market.index') }}" method="GET" class="mb-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-lg-4">
                            <label for="search-input" class="form-label">Search</label>
                            <input type="search" name="search" id="search-input" class="form-control"
                                placeholder="Search by party, bill no, product..." value="{{ request('search') }}" />
                        </div>
                        <div class="col-12 col-sm-6 col-lg-2">
                            <label for="payment_status" class="form-label">Payment Status</label>
                            <select name="payment_status" id="payment_status" class="form-select">
                                <option value="">All</option>
                                <option value="paid" @selected(request('payment_status') === 'paid')>Paid</option>
                                <option value="unpaid" @selected(request('payment_status') === 'unpaid')>Unpaid</option>
                            </select>
                        </div>
                        <div class="col-6 col-lg-2">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control"
                                value="{{ request('start_date') }}">
                        </div>
                        <div class="col-6 col-lg-2">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control"
                                value="{{ request('end_date') }}">
                        </div>
                        <div class="col-6 col-lg-1 d-flex">
                            <button type="submit" class="btn btn-info w-100"><i class="fas fa-filter"></i></button>
                        </div>
                        <div class="col-6 col-lg-1 d-flex">
                            <a href="{{ route('vashi-market.index') }}" class="btn btn-outline-secondary w-100"
                                title="Reset filters">
                                <i class="fas fa-rotate-left"></i>
                                <span class="d-lg-none ms-1">Reset</span>
                            </a>
                        </div>
                    </div>
                </form>
                <div class="d-flex justify-content-end gap-2 mb-4 flex-wrap">
                    <a href="{{ route('vashi-market.payments.create') }}" class="btn btn-success text-nowrap">
                        <i class="fas fa-money-check-alt me-1"></i> Pay Multiple Bills
                    </a>
                    <a href="{{ route('vashi-market.create') }}" class="btn btn-primary text-nowrap"><i
                            class="fas fa-plus me-1"></i> Add Bill</a>
                </div>

                <div id="data-list" class="d-flex flex-column gap-3" style="max-height: 70vh; overflow-y: auto"
                    data-next-cursor="{{ $bills->nextCursor()?->encode() }}"
                    data-has-more="{{ $bills->hasMorePages() ? '1' : '0' }}">
                    @include('admin.vashiMarketBillCards', ['bills' => $bills])

                    <div id="skeleton-loader" class="d-none" aria-hidden="true">
                        @for ($i = 0; $i < 3; $i++)
                            <div class="card skeleton-card">
                                <div class="card-body">
                                    @for ($row = 0; $row < 4; $row++)
                                        <div class="skeleton-row">
                                            <div class="skeleton-icon"></div>
                                            <div class="skeleton-text">
                                                <div class="skeleton-label skeleton-shimmer"></div>
                                                <div class="skeleton-value skeleton-shimmer"></div>
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        @endfor
                    </div>

                    <div id="no-results" class="text-center text-muted p-4" style="display: none;">No bills found matching
                        your search.</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('search-input');
            const filterForm = searchInput.closest('form');
            const paymentStatusInput = document.getElementById('payment_status');
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            const dataList = document.getElementById('data-list');
            const skeletonLoader = document.getElementById('skeleton-loader');
            const noResultsMessage = document.getElementById('no-results');
            const defaultNoResultsMessage = noResultsMessage.textContent.trim();

            let nextCursor = dataList.dataset.nextCursor || null;
            let hasMore = dataList.dataset.hasMore === '1';
            let isLoading = false;
            let searchTimer = null;
            let activeRequest = null;
            let requestId = 0;

            const setLoading = (isVisible) => {
                skeletonLoader.classList.toggle('d-none', !isVisible);
                skeletonLoader.setAttribute('aria-hidden', String(!isVisible));
            };

            const removeBillCards = () => {
                dataList.querySelectorAll('.data-card-link').forEach((card) => card.remove());
            };

            const appendFilterParams = (params) => {
                if (searchInput.value.trim()) {
                    params.set('search', searchInput.value.trim());
                }
                if (paymentStatusInput.value) {
                    params.set('payment_status', paymentStatusInput.value);
                }
                if (startDateInput.value) {
                    params.set('start_date', startDateInput.value);
                }
                if (endDateInput.value) {
                    params.set('end_date', endDateInput.value);
                }
            };

            const updateBrowserUrl = () => {
                const url = new URL(filterForm.action, window.location.origin);
                const params = new URLSearchParams();
                appendFilterParams(params);
                url.search = params.toString();
                window.history.replaceState({}, '', url);
            };

            const getBillsUrl = () => {
                const url = new URL(filterForm.action, window.location.origin);
                const params = new URLSearchParams();
                appendFilterParams(params);

                if (nextCursor) {
                    params.set('cursor', nextCursor);
                }

                url.search = params.toString();
                return url.toString();
            };

            const showNoResultsIfNeeded = () => {
                const hasCards = dataList.querySelector('.data-card-link') !== null;
                noResultsMessage.style.display = hasCards ? 'none' : 'block';
            };

            const loadBills = async (reset = false) => {
                if (isLoading && !reset) {
                    return;
                }
                if (!reset && !hasMore) {
                    return;
                }

                if (reset) {
                    activeRequest?.abort();
                    requestId++;
                    nextCursor = null;
                    hasMore = true;
                    removeBillCards();
                    noResultsMessage.style.display = 'none';
                    updateBrowserUrl();
                }

                const currentRequestId = ++requestId;
                const controller = new AbortController();
                activeRequest = controller;
                isLoading = true;
                noResultsMessage.textContent = defaultNoResultsMessage;
                setLoading(true);

                try {
                    const response = await fetch(getBillsUrl(), {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        signal: controller.signal,
                    });

                    if (!response.ok) {
                        throw new Error(`Request failed with status ${response.status}`);
                    }

                    const result = await response.json();

                    if (currentRequestId !== requestId) {
                        return;
                    }

                    if (result.html) {
                        skeletonLoader.insertAdjacentHTML('beforebegin', result.html);
                    }

                    nextCursor = result.next_cursor || null;
                    hasMore = Boolean(result.has_more);
                    showNoResultsIfNeeded();

                    // If one page does not fill the scroll area, fetch the next
                    // page so the user can continue scrolling naturally.
                    if (hasMore && dataList.scrollHeight <= dataList.clientHeight + 80) {
                        requestAnimationFrame(() => loadBills());
                    }
                } catch (error) {
                    if (error.name !== 'AbortError' && currentRequestId === requestId) {
                        console.error('Unable to load bills:', error);
                        noResultsMessage.textContent = 'Unable to load bills. Please try again.';
                        noResultsMessage.style.display = 'block';
                    }
                } finally {
                    if (currentRequestId === requestId) {
                        isLoading = false;
                        activeRequest = null;
                        setLoading(false);
                    }
                }
            };

            filterForm.addEventListener('submit', function (event) {
                event.preventDefault();
                clearTimeout(searchTimer);
                loadBills(true);
            });

            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => loadBills(true), 350);
            });

            paymentStatusInput.addEventListener('change', function () {
                clearTimeout(searchTimer);
                loadBills(true);
            });

            dataList.addEventListener('scroll', function () {
                const remainingScroll = dataList.scrollHeight - dataList.scrollTop - dataList.clientHeight;

                if (remainingScroll < 180) {
                    loadBills();
                }
            });

            showNoResultsIfNeeded();
        });
    </script>
@endsection