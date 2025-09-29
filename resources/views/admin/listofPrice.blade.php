@extends('layouts.app')
@section('content')
    <div class="page-content container-fluid">
        <div class="card-body">
            <div class="card-header">
                <h3 class="text-center" style="color: #ff6347; font-weight: bold">
                    <u class="mb-1"> Sandip Oil Depo General Store Prices</u>
                </h3>
            </div>
            <!-- Tabs for categories -->
            <ul class="nav nav-tabs justify-content-center mb-3" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general"
                        type="button" role="tab" aria-controls="general" aria-selected="true">
                        <i class="fas fa-box-open me-1"></i>
                        Grocery
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="oil-tab" data-bs-toggle="tab" data-bs-target="#oil" type="button"
                        role="tab" aria-controls="oil" aria-selected="false">
                        <i class="fas fa-oil-can me-1"></i>
                        Oil
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="masala-tab" data-bs-toggle="tab" data-bs-target="#masala" type="button"
                        role="tab" aria-controls="masala" aria-selected="false">
                        <i class="fas fa-mortar-pestle me-1"></i>
                        Masala
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="other-tab" data-bs-toggle="tab" data-bs-target="#other" type="button"
                        role="tab" aria-controls="other" aria-selected="false">
                        <i class="fas fa-box me-1"></i>
                        Other
                    </button>
                </li>
            </ul>
            <div class="row justify-content-center mb-3">
                <div class="col-12 col-md-8 col-lg-6">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search products..." />
                        <a href="{{ route('admin.product.create') }}" class="btn btn-primary text-nowrap">
                            <i class="fas fa-plus d-md-none"></i>
                            <span class="d-none d-md-inline"><i class="fas fa-plus me-1"></i> Add Product</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Session Messages --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Tab Content -->
            <div class="tab-content" id="myTabContent">
                <!-- General Tab Content -->
                <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                    <header>
                        <h3 class="text-center" style="color: #ff6347">
                            General Material Price List
                        </h3>
                    </header>

                    <div class="table-container">
                        <table class="table table-hover data-table" id="generalTable">
                            <thead>
                                <tr>
                                    <th scope="col">Purchase Rate</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">250g / ₹</th>
                                    <th scope="col">500g / ₹</th>
                                    <th scope="col">1kg / ₹</th>
                                    <th>edit</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach ($groceryProducts as $product)
                                    @if ($product->type_product == 'grocery')
                                        @php
                                            // 12% extra for selling price
                                            $salePrice = $product->purchase_price * 1.12;

                                            // 1kg = sale price
                                            $price1kg = round($salePrice, 2);

                                            // 500g = half of 1kg
                                            $price500g = round($salePrice / 2, 2);

                                            // 250g = quarter of 1kg
                                            $price250g = round($salePrice / 4, 2);
                                        @endphp
                                        <tr>
                                            <td>₹ {{ $product->purchase_price }}</td>
                                            <td><b>{{ $product->name }}</b></td>
                                            <td>₹ {{ $price250g }}</td>
                                            <td>₹ {{ $price500g }}</td>
                                            <td>₹ {{ $price1kg }}</td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Oil Tab Content -->
                <!-- Oil Tab Content -->
                <div class="tab-pane fade" id="oil" role="tabpanel" aria-labelledby="oil-tab">
                    <header>
                        <h3 class="text-center" style="color: #ff6347; font-weight: bold">
                            Oil Price List
                        </h3>
                    </header>

                    @foreach ($oilProducts as $brand => $products)
                        <div class="card mb-3">
                            <div class="card-header bg-transparent d-flex justify-content-between align-items-center"
                                data-bs-toggle="collapse" href="#brand{{ Str::slug($brand) }}" role="button"
                                aria-expanded="false" aria-controls="brand{{ Str::slug($brand) }}">
                                <h5 class="mb-0">{{ $brand }} ({{ $products->first()->name }})</h5>
                                <i class="fas fa-chevron-down"></i>
                            </div>

                            <div class="collapse" id="brand{{ Str::slug($brand) }}">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered text-center">
                                            <thead>
                                                <tr class="table-light">
                                                    <th>Purchase</th>
                                                    <th>Weight</th>
                                                    <th>Sale</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($products as $product)
                                                    @if ($product->size_750ml)
                                                        <tr>
                                                            <td>₹ {{ $product->size_750ml }}</td>
                                                            <td><b>750ml</b>(7%)</td>
                                                            <td>₹ {{ round($product->size_750ml * 1.07) }}</td>
                                                        </tr>
                                                    @endif
                                                    @if ($product->size_1L)
                                                        <tr>
                                                            <td>₹ {{ $product->size_1L }}</td>
                                                            <td><b>1L</b>(7%)</td>
                                                            <td>₹ {{ round($product->size_1L * 1.07) }}</td>
                                                        </tr>
                                                    @endif
                                                    @if ($product->size_3L)
                                                        <tr>
                                                            <td>₹ {{ $product->size_3L }}</td>
                                                            <td><b>3L</b>(7%)</td>
                                                            <td>₹ {{ round($product->size_3L * 1.07) }}</td>
                                                        </tr>
                                                    @endif
                                                    @if ($product->size_5L)
                                                        <tr>
                                                            <td>₹ {{ $product->size_5L }}</td>
                                                            <td><b>5L</b>(7%)</td>
                                                            <td>₹ {{ round($product->size_5L * 1.07) }}</td>
                                                        </tr>
                                                    @endif
                                                    @if ($product->size_15L_tin)
                                                        <tr>
                                                            <td>₹ {{ $product->size_15L_tin }}</td>
                                                            <td><b>15L (Tin)</b>(5%)</td>
                                                            <td>₹ {{ round($product->size_15L_tin * 1.05) }}</td>
                                                        </tr>
                                                    @endif
                                                    @if ($product->size_15L_jar)
                                                        <tr>
                                                            <td>₹ {{ $product->size_15L_jar }}</td>
                                                            <td><b>15L (Jar)</b>(5%)</td>
                                                            <td>₹ {{ round($product->size_15L_jar * 1.05) }}</td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Masala Tab Content -->
                <div class="tab-pane fade" id="masala" role="tabpanel" aria-labelledby="masala-tab">
                    <header>
                        <h3 class="text-center" style="color: #ff6347; font-weight: bold">
                            Masala Price List
                        </h3>
                    </header>
                    <div class="table-container">
                        <table class="table table-hover data-table" id="masalaTable">
                            <thead>
                                <tr>
                                    <th scope="col">Purchase Rate</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">10g</th>
                                    <th scope="col">50g</th>
                                    <th scope="col">250g</th>
                                    <th scope="col">1Kg</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($masalaProducts as $product)
                                    @if ($product->type_product == 'masala')
                                        @php
                                            // For 1kg & 250g -> 15% extra
                                            $salePrice15 = $product->purchase_price * 1.15;

                                            $price1kg = round($salePrice15, 2);
                                            $price250g = round($salePrice15 / 4, 2);

                                            // For 10g & 50g -> 20% extra
                                            $salePrice20 = $product->purchase_price * 1.20;

                                            // Since 1kg = 1000g, divide accordingly
                                            $price50g = round($salePrice20 / (1000 / 50), 2);
                                            $price10g = round($salePrice20 / (1000 / 10), 2);
                                        @endphp
                                        <tr>
                                            <td>₹ {{ $product->purchase_price }}</td>
                                            <td><b>{{ $product->name }}</b></td>
                                            <td>₹ {{ $price10g }}</td>
                                            <td>₹ {{ $price50g }}</td>
                                            <td>₹ {{ $price250g }}</td>
                                            <td>₹ {{ $price1kg }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Other Tab Content -->
                <div class="tab-pane fade" id="other" role="tabpanel" aria-labelledby="other-tab">
                    <header>
                        <h3 class="text-center" style="color: #ff6347; font-weight: bold">
                            Other Products Price List
                        </h3>
                    </header>
                    <div class="table-container">
                        <table class="table table-hover data-table" id="otherTable">
                            <thead>
                                <tr>
                                    <th scope="col">Purchase Rate</th>
                                    <th scope="col">Name of Product</th>
                                    <th scope="col">Sale Amount (1Kg or 1 quantity)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($otherProducts as $product)
                                    @if ($product->type_product == 'other')
                                        <tr>
                                            <td>₹ {{ $product->purchase_price }}</td>
                                            <td><b>{{ $product->name }}</b></td>
                                            <td>₹ {{ round($product->purchase_price * 1.25) }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('scripts')
    <script>
        // The modal-related javascript is no longer needed here.
    </script>

@endsection