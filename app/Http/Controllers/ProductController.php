<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Inventory module — solves Problem 1 (Inaccurate Inventory Monitoring).
     * Every product's quantity_on_hand is a system-tracked number, not an
     * estimate from "sight and memory". low_stock badge replaces the
     * reactive, no-threshold restocking habit described in the paper.
     */
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->boolean('low_stock_only')) {
            $query->lowStock();
        }

        $products = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sku' => 'required|string|max:50|unique:products,sku',
            'name' => 'required|string|max:150',
            'category' => 'nullable|string|max:100',
            'unit' => 'required|string|max:20',
            'selling_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'quantity_on_hand' => 'required|integer|min:0',
            'reorder_point' => 'required|integer|min:0',
        ]);

        Product::create($data);

        return redirect()->route('products.index')->with('status', 'Product added successfully.');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'sku' => 'required|string|max:50|unique:products,sku,' . $product->id,
            'name' => 'required|string|max:150',
            'category' => 'nullable|string|max:100',
            'unit' => 'required|string|max:20',
            'selling_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'quantity_on_hand' => 'required|integer|min:0',
            'reorder_point' => 'required|integer|min:0',
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
