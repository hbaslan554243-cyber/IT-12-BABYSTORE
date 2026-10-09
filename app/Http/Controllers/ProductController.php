<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%')
                  ->orWhere('brand', 'like', '%' . $request->search . '%')
                  ->orWhere('variant', 'like', '%' . $request->search . '%')
                  ->orWhere('size', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->get('stock_filter') === 'low') {
            $query->lowStock();
        } elseif ($request->get('stock_filter') === 'out') {
            $query->outOfStock();
        }

        $products = $query->orderBy('name')->paginate(15)->withQueryString();

        $outOfStockCount = Product::outOfStock()->count();
        $lowStockCount = Product::lowStock()->count();

        return view('products.index', compact('products', 'outOfStockCount', 'lowStockCount'));
    }

    public function create()
    {
        return view('products.create', ['categories' => Product::CATEGORIES, 'units' => Product::UNITS, 'sizes' => Product::SIZES]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'brand' => 'nullable|string|max:150',
            'variant' => 'nullable|string|max:100',
            'size' => ['nullable', Rule::in(array_keys(Product::SIZES))],
            'category' => 'required|string|max:100',
            'unit' => 'required|string|max:20',
            'selling_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'quantity_on_hand' => 'required|integer|min:0',
            'reorder_point' => 'required|integer|min:0',
            'expiration_date' => 'nullable|date',
        ]);

        $data['sku'] = Product::generateSku($data['category']);
        $product = Product::create($data);

        return redirect()->route('products.index')->with('status', 'Product added successfully. SKU: ' . $product->sku);
    }

    public function edit(Product $product)
    {
        return view('products.edit', ['product' => $product, 'categories' => Product::CATEGORIES, 'units' => Product::UNITS, 'sizes' => Product::SIZES]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'brand' => 'nullable|string|max:150',
            'variant' => 'nullable|string|max:100',
            // Allows the standard sizes, plus whatever this product already
            // has stored (older products may hold a free-text size from
            // before this became a dropdown) so editing never wipes it.
            'size' => ['nullable', Rule::in(array_filter(array_merge(array_keys(Product::SIZES), [$product->size])))],
            'category' => 'required|string|max:100',
            'unit' => 'required|string|max:20',
            'selling_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'quantity_on_hand' => 'required|integer|min:0',
            'reorder_point' => 'required|integer|min:0',
            'expiration_date' => 'nullable|date',
        ]);

        $product->update($data);

        return redirect()->route('products.index')->with('status', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('status', 'Product removed.');
    }
}