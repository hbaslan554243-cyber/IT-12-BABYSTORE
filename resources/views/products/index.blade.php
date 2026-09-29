@extends('layouts.app')
@section('title', 'Inventory')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Inventory <small class="text-muted fs-6"></small></h2>
        @if(auth()->user()->isOwner())
            <a href="{{ route('products.create') }}" class="btn btn-danger">+ Add Product</a>
        @endif
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by name or SKU">
        </div>
        <div class="col-md-3">
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="low_stock_only" value="1" id="lowstock" {{ request('low_stock_only') ? 'checked' : '' }} onchange="this.form.submit()">
                <label class="form-check-label" for="lowstock">Low stock only</label>
            </div>
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-secondary w-100">Filter</button>
        </div>
    </form>

    <table class="table table-bordered bg-white">
        <thead>
        <tr>
            <th>SKU</th><th>Name</th><th>Category</th><th>Unit</th>
            <th>Price</th><th>On Hand</th><th>Reorder Pt.</th><th>Status</th><th></th>
        </tr>
        </thead>
        <tbody>
        @forelse($products as $product)
            <tr class="{{ $product->isLowStock() ? 'low-stock' : '' }}">
                <td>{{ $product->sku }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->category }}</td>
                <td>{{ $product->unit }}</td>
                <td>₱{{ number_format($product->selling_price, 2) }}</td>
                <td>{{ $product->quantity_on_hand }}</td>
                <td>{{ $product->reorder_point }}</td>
                <td>
                    @if($product->isLowStock())
                        <span class="badge bg-warning text-dark">Low Stock</span>
                    @else
                        <span class="badge bg-success">OK</span>
                    @endif
                </td>
                <td>
                    @if(auth()->user()->isOwner())
                        <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this product?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    @else
                        <span class="text-muted small">View only</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="9" class="text-center text-muted">No products found.</td></tr>
        @endforelse
        </tbody>
    </table>

    {{ $products->links() }}
@endsection
