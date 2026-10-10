@extends('layouts.app')
@section('title', '')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Inventory <small class="text-muted fs-6"></small></h2>
        @if(auth()->user()->isOwner())
            <a href="{{ route('products.create') }}" class="btn btn-danger">+ Add Product</a>
        @endif
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <a href="{{ route('products.index') }}" class="text-decoration-none">
                <div class="card p-3 stat-card">
                    <div class="text-muted small">Total Products</div>
                    <div class="fs-4 fw-bold">{{ \App\Models\Product::count() }}</div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('products.index', ['stock_filter' => 'low']) }}" class="text-decoration-none">
                <div class="card p-3 stat-card" style="border-left-color:#F4C79A">
                    <div class="text-muted small">Low Stock</div>
                    <div class="fs-4 fw-bold">{{ $lowStockCount }}</div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('products.index', ['stock_filter' => 'out']) }}" class="text-decoration-none">
                <div class="card p-3 stat-card" style="border-left-color:#E88989">
                    <div class="text-muted small">Out of Stock</div>
                    <div class="fs-4 fw-bold">{{ $outOfStockCount }}</div>
                </div>
            </a>
        </div>
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by name, SKU, or brand">
        </div>
        <div class="col-md-4">
            <select name="stock_filter" class="form-select" onchange="this.form.submit()">
                <option value="">All stock levels</option>
                <option value="low" {{ request('stock_filter') === 'low' ? 'selected' : '' }}>Low stock only</option>
                <option value="out" {{ request('stock_filter') === 'out' ? 'selected' : '' }}>Out of stock only</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-secondary w-100">Filter</button>
        </div>
    </form>

    <div class="table-responsive">
    <table class="table table-bordered bg-white">
        <thead>
        <tr>
            <th>SKU</th><th>Name</th><th>Brand</th><th>Category</th><th>Unit</th>
            <th>Price</th><th>On Hand</th><th>Reorder Pt.</th><th>Expiration</th><th>Status</th><th></th>
        </tr>
        </thead>
        <tbody>
        @forelse($products as $product)
            <tr class="{{ $product->isOutOfStock() ? 'out-of-stock-row' : ($product->isLowStock() ? 'low-stock' : '') }}">
                <td>{{ $product->sku }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->brand ?? '—' }}</td>
                <td>{{ $product->category }}</td>
                <td>{{ $product->unit }}</td>
                <td>₱{{ number_format($product->selling_price, 2) }}</td>
                <td>{{ $product->quantity_on_hand }}</td>
                <td>{{ $product->reorder_point }}</td>
                <td>
                    @if($product->expiration_date)
                        {{ $product->expiration_date->format('M d, Y') }}
                        @if($product->isExpired())
                            <span class="badge bg-danger-soft">Expired</span>
                        @elseif($product->isExpiringSoon())
                            <span class="badge bg-warning">Expiring soon</span>
                        @endif
                    @else
                        —
                    @endif
                </td>
                <td>
                    @if($product->isOutOfStock())
                        <span class="badge bg-danger-soft">Out of Stock</span>
                    @elseif($product->isLowStock())
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
            <tr><td colspan="11" class="text-center text-muted">No products found.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>

    {{ $products->links() }}
@endsection
