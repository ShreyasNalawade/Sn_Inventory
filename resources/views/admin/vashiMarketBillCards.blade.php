@foreach ($bills as $bill)
    <a href="{{ route('vashi-market.showBillDetails', $bill->id) }}" class="data-card-link text-decoration-none">
        <div class="card data-card">
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="d-flex align-items-center mb-2">
                        <i class="fas fa-calendar-alt fa-fw me-2 text-muted"></i>
                        <strong>Bill Date:</strong>&nbsp;
                        <span class="bill-date">{{ \Carbon\Carbon::parse($bill->bill_date)->format('d/m/Y') }}</span>
                    </li>
                    <li class="d-flex align-items-center mb-2">
                        <i class="fas fa-box fa-fw me-2 text-muted"></i>
                        <strong>Products:</strong>&nbsp;
                        <span class="product-name">{{ $bill->products->pluck('product_name')->implode(', ') }}</span>
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
