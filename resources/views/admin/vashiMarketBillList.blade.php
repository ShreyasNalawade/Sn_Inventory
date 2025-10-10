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
                        <div class="col-md-5">
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
                        <div class="col-md-1 d-flex">
                            <button type="submit" class="btn btn-info w-100"><i class="fas fa-filter"></i></button>
                        </div>
                    </div>
                </form>
                <div class="d-flex justify-content-end mb-4">
                    <a href="{{ route('vashi-market.create') }}" class="btn btn-primary text-nowrap"><i
                            class="fas fa-plus me-1"></i> Add Bill</a>
                </div>

                <div id="data-list" class="d-flex flex-column gap-3" style="max-height: 70vh; overflow-y: auto">
                    @foreach ($bills as $bill)
                        <a href="{{ route('vashi-market.showBillDetails', $bill->id) }}" class="data-card-link">
                            <div class="card data-card">
                                <div class="card-body">
                                    {{-- The content inside the card remains the same --}}
                                    <ul class="list-unstyled mb-0">
                                        <li class="d-flex align-items-center mb-2">
                                            <i class="fas fa-calendar-alt fa-fw me-2 text-muted"></i>
                                            <strong>Bill Date:</strong>&nbsp;
                                            <span
                                                class="bill-date">{{ \Carbon\Carbon::parse($bill->bill_date)->format('d/m/Y') }}</span>
                                        </li>
                                        <li class="d-flex align-items-center mb-2">
                                            <i class="fas fa-box fa-fw me-2 text-muted"></i>
                                            <strong>Products:</strong>&nbsp;
                                            <span
                                                class="product-name">{{ $bill->products->pluck('product_name')->implode(', ') }}</span>
                                        </li>
                                        <li class="d-flex align-items-center mb-2">
                                            <i class="fas fa-user-tie fa-fw me-2 text-muted"></i>
                                            <strong>Party:</strong>&nbsp;
                                            <span class="party-name">{{ $bill->party_name }}</span>
                                        </li>
                                        <li class="d-flex align-items-center mb-2">
                                            <i class="fas fa-file-invoice fa-fw me-2 text-muted"></i>
                                            <strong>Bill No:</strong>&nbsp;
                                            <span class="bill-no">{{ $bill->bill_no }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </a>
                    @endforeach
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
            const dataList = document.getElementById('data-list');
            const billCards = dataList.querySelectorAll('.data-card-link');
            const noResultsMessage = document.getElementById('no-results');

            function checkInitialState() {
                if (billCards.length === 0) {
                    noResultsMessage.style.display = 'block';
                }
            }
            function filterBills() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                let visibleCount = 0;

                billCards.forEach(cardLink => {
                    const billDate = cardLink.querySelector('.bill-date').textContent.toLowerCase();
                    const partyName = cardLink.querySelector('.party-name').textContent.toLowerCase();
                    const billNo = cardLink.querySelector('.bill-no').textContent.toLowerCase();
                    const productName = cardLink.querySelector('.product-name').textContent.toLowerCase();

                    if (partyName.includes(searchTerm) || billNo.includes(searchTerm) || productName.includes(searchTerm) || billDate.includes(searchTerm)) {
                        cardLink.style.display = '';
                        visibleCount++;
                    } else {
                        cardLink.style.display = 'none';
                    }
                });

                if (noResultsMessage) {
                    noResultsMessage.style.display = visibleCount === 0 ? 'block' : 'none';
                }
            }

            searchInput.addEventListener('keyup', filterBills);
            checkInitialState();
        });
    </script>
@endsection