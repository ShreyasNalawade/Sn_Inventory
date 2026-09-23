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
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
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
                <form action="{{ route('vashi-market.index') }}" method="GET" class="mb-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="search-input" class="form-label">Search</label>
                            <input type="search" name="search" id="search-input" class="form-control"
                                placeholder="Search by party, bill no, product..." value="{{ request('search') }}" />
                        </div>
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control"
                                value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control"
                                value="{{ request('end_date') }}">
                        </div>
                        <div class="col-6 col-md-1 d-flex">
                            <button type="submit" class="btn btn-info w-100"><i class="fas fa-filter"></i></button>
                        </div>
                        <div class="col-6 col-md-1 d-flex">
                            <a href="{{ route('vashi-market.index') }}" class="btn btn-outline-secondary w-100"
                                title="Reset filters">
                                <i class="fas fa-rotate-left"></i>
                                <span class="d-md-none ms-1">Reset</span>
                            </a>
                        </div>
                    </div>
                </form>
                <div class="d-flex justify-content-end mb-4">
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

            const updateBrowserUrl = () => {
                const url = new URL(filterForm.action, window.location.origin);
                const params = new URLSearchParams();

                if (searchInput.value.trim()) {
                    params.set('search', searchInput.value.trim());
                }
                if (startDateInput.value) {
                    params.set('start_date', startDateInput.value);
                }
                if (endDateInput.value) {
                    params.set('end_date', endDateInput.value);
                }

                url.search = params.toString();
                window.history.replaceState({}, '', url);
            };

            const getBillsUrl = () => {
                const url = new URL(filterForm.action, window.location.origin);
                const params = new URLSearchParams();

                if (searchInput.value.trim()) {
                    params.set('search', searchInput.value.trim());
                }
                if (startDateInput.value) {
                    params.set('start_date', startDateInput.value);
                }
                if (endDateInput.value) {
                    params.set('end_date', endDateInput.value);
                }
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