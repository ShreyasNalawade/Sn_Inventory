@extends('layouts.app')

@section('content')
    <div class="page-content container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="text-center" style="color: #ff6347; font-weight: bold">
                    @if (isset($product))
                        Edit Product
                    @else
                        Add New Product
                    @endif
                </h3>
            </div>
            <div class="card-body">
                <form
                    action="{{ isset($product) ? route('admin.product.update', $product->id) : route('admin.product.store') }}"
                    method="POST">
                    @csrf
                    @if (isset($product))
                        @method('PUT')
                    @endif

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

                    {{-- Category Selector --}}
                    <div class="row justify-content-center">
                        <div class="col-md-8 col-lg-6 mb-4">
                            <label for="productType" class="form-label">Product Category <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="productType" name="type_product" required>
                                <option value="" disabled>-- Select a Category --</option>
                                <option value="grocery"
                                    {{ old('type_product', isset($product) ? $product->type_product : '') == 'grocery' ? 'selected' : '' }}>
                                    Grocery</option>
                                <option value="oil"
                                    {{ old('type_product', isset($product) ? $product->type_product : '') == 'oil' ? 'selected' : '' }}>
                                    Oil</option>
                                <option value="masala"
                                    {{ old('type_product', isset($product) ? $product->type_product : '') == 'masala' ? 'selected' : '' }}>
                                    Masala</option>
                                <option value="other"
                                    {{ old('type_product', isset($product) ? $product->type_product : '') == 'other' ? 'selected' : '' }}>
                                    Other</option>
                            </select>
                        </div>
                    </div>

                    {{-- Dynamic Form Fields --}}
                    <div id="form-fields-container">
                        {{-- Fields for Oil Products --}}
                        <div id="oil-fields" class="d-none">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="oilProductName" class="form-label">Oil Product Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" id="oilProductName"
                                        value="{{ old('name', isset($product) ? $product->name : '') }}" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="oilBrandName" class="form-label">Brand Name</label>
                                    <input type="text" name="brand_name" class="form-control" id="oilBrandName"
                                        value="{{ old('brand_name', isset($product) ? $product->brand_name : '') }}" />
                                </div>
                            </div>
                            <hr>
                            <h6 class="text-center mb-3">Enter Purchase Prices (Optional)</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="price750ml" class="form-label">750ml Price</label>
                                    <input type="number" name="size_750ml" class="form-control" id="price750ml"
                                        step="0.01"
                                        value="{{ old('size_750ml', isset($product) ? $product->size_750ml : '') }}" />
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="price1L" class="form-label">1L Price</label>
                                    <input type="number" name="size_1L" class="form-control" id="price1L"
                                        step="0.01"
                                        value="{{ old('size_1L', isset($product) ? $product->size_1L : '') }}" />
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="price5L" class="form-label">5L Price</label>
                                    <input type="number" name="size_5L" class="form-control" id="price5L"
                                        step="0.01"
                                        value="{{ old('size_5L', isset($product) ? $product->size_5L : '') }}" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="price15Ltin" class="form-label">15L (tin) Price</label>
                                    <input type="number" name="size_15L_tin" class="form-control" id="price15Ltin"
                                        step="0.01"
                                        value="{{ old('size_15L_tin', isset($product) ? $product->size_15L_tin : '') }}" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="price15Ljar" class="form-label">15L (jar) Price</label>
                                    <input type="number" name="size_15L_jar" class="form-control" id="price15Ljar"
                                        step="0.01"
                                        value="{{ old('size_15L_jar', isset($product) ? $product->size_15L_jar : '') }}" />
                                </div>
                            </div>
                        </div>

                        {{-- Fields for Other Products --}}
                        <div id="general-fields" class="d-none">
                            <div class="row justify-content-center">
                                <div class="col-md-8 col-lg-6">
                                    <div class="mb-3">
                                        <label for="generalProductName" class="form-label">Product Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control"
                                            id="generalProductName"
                                            value="{{ old('name', isset($product) ? $product->name : '') }}" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="purchasePrice" class="form-label">Purchase Price <span
                                                class="text-danger">*</span></label>
                                        <input type="number" name="purchase_price" class="form-control"
                                            id="purchasePrice" step="0.01"
                                            value="{{ old('purchase_price', isset($product) ? $product->purchase_price : '') }}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <a href="{{ route('admin.listofPrice') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            @if (isset($product))
                                Update Product
                            @else
                                Save Product
                            @endif
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productTypeSelect = document.getElementById('productType');
            const oilFields = document.getElementById('oil-fields');
            const generalFields = document.getElementById('general-fields');
            const oilNameInput = document.getElementById('oilProductName');
            const generalNameInput = document.getElementById('generalProductName');
            const purchasePriceInput = document.getElementById('purchasePrice');

            // Function to disable all dynamic inputs
            function disableAllFields() {
                oilFields.querySelectorAll('input').forEach(input => input.disabled = true);
                generalFields.querySelectorAll('input').forEach(input => input.disabled = true);
                oilNameInput.name = '';
                generalNameInput.name = '';
            }

            function toggleFields() {
                disableAllFields(); // Start by disabling all fields

                const selectedType = productTypeSelect.value;

                if (selectedType === 'oil') {
                    oilFields.classList.remove('d-none');
                    generalFields.classList.add('d-none');
                    oilFields.querySelectorAll('input').forEach(input => input.disabled = false);
                    oilNameInput.name = 'name';
                } else if (selectedType) { // For 'grocery', 'masala', 'other'
                    generalFields.classList.remove('d-none');
                    oilFields.classList.add('d-none');
                    generalFields.querySelectorAll('input').forEach(input => input.disabled = false);
                    generalNameInput.name = 'name';
                } else {
                    oilFields.classList.add('d-none');
                    generalFields.classList.add('d-none');
                }
            }

            productTypeSelect.addEventListener('change', toggleFields);

            // Run on page load to set the correct initial state
            toggleFields();
        });
    </script>
@endsection