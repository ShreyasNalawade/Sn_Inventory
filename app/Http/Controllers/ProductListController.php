<?php

namespace App\Http\Controllers;

use App\Models\Product;

use Illuminate\Http\Request;   // ✅ Correct import

class ProductListController extends Controller
{
    //
    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        return view('admin.addProduct');
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        // General validation rules
        $rules = [
            'type_product' => 'required|string|in:grocery,oil,masala,other',
            'name' => 'required|string|max:255',
        ];

        // Add specific rules based on product type
        if ($request->input('type_product') === 'oil') {
            $rules += [
                'brand_name' => 'nullable|string|max:255',
                'size_750ml' => 'nullable|numeric|min:0',
                'size_1L' => 'nullable|numeric|min:0',
                'size_5L' => 'nullable|numeric|min:0',
                'size_15L_tin' => 'nullable|numeric|min:0',
                'size_15L_jar' => 'nullable|numeric|min:0',
            ];
        } else {
            $rules['purchase_price'] = 'required|numeric|min:0';
        }

        $validatedData = $request->validate($rules);

        // Check for duplicates
        if (
            Product::where('name', $validatedData['name'])
                ->where('brand_name', $request->input('brand_name')) // brand_name is only for oil
                ->where('type_product', $validatedData['type_product'])
                ->exists()
        ) {
            return back()->with('error', 'A product with this name and type already exists.')->withInput();
        }

        Product::create($validatedData);

        return redirect()->route('admin.listofPrice')->with('success', 'Product added successfully!');
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        return view('admin.addProduct', compact('product'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $rules = [
            'type_product' => 'required|string|in:grocery,oil,masala,other',
            'name' => 'required|string|max:255',
        ];

        if ($request->input('type_product') === 'oil') {
            $rules += [
                'brand_name' => 'nullable|string|max:255',
                'size_750ml' => 'nullable|numeric|min:0',
                'size_1L' => 'nullable|numeric|min:0',
                'size_5L' => 'nullable|numeric|min:0',
                'size_15L_tin' => 'nullable|numeric|min:0',
                'size_15L_jar' => 'nullable|numeric|min:0',
            ];
        } else {
            $rules['purchase_price'] = 'required|numeric|min:0';
        }

        $validatedData = $request->validate($rules);

        $product->update($validatedData);

        return redirect()->route('admin.listofPrice')->with('success', 'Product updated successfully!');
    }
}
