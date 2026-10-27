@extends('layouts.app')
@section('title', '')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Inventory <small class="text-muted fs-6"></small></h2>
        <div class="d-flex gap-2">
            @if(auth()->user()->isOwner())
            <a href="{{ route('products.create') }}" class="btn btn-danger">+ Add Product</a>
            @endif
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <a href="{{ route('products.index') }}" class="text-decoration-none">
                <div class="card p-3 stat-card">
                    <div class="text-muted small">Total Products</div>
                    <div class="fs-4 fw-bold">{{ $activeProductCount }}</div>
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
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name, SKU, brand, variant, or size">
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
        <div class="col-md-2">
            <button type="button" class="btn btn-outline-secondary w-100" data-bs-toggle="modal" data-bs-target="#archivedProductsModal">
                Archived <span class="badge bg-secondary">{{ $archivedProducts->count() }}</span>
            </button>
        </div>
    </form>

    <div class="table-responsive">
    <table class="table table-bordered bg-white">
        <thead>
        <tr>
            <th>SKU</th><th>Product</th><th>Category</th><th>Unit</th>
            <th>Price</th><th>On Hand</th><th>Reorder Pt.</th><th>Expiration</th><th>Status</th><th></th>
        </tr>
        </thead>
        <tbody>
        @forelse($products as $product)
            <tr class="{{ $product->isOutOfStock() ? 'out-of-stock-row' : ($product->isLowStock() ? 'low-stock' : '') }}">
                <td>{{ $product->sku }}</td>
                <td>
                    <div>{{ $product->name }}</div>
                    @if($product->productLabel(false, true))
                        <small class="text-muted">{{ $product->productLabel(false, true) }}</small>
                    @endif
                </td>
                <td>{{ $product->displayCategory() }}</td>
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
                        <form action="{{ route('products.archive', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Archive this product? It will be removed from active inventory and POS, while sales history is preserved.')">
                            @csrf
                            <button class="btn btn-sm btn-outline-danger">Archive</button>
                        </form>
                    @else
                        <span class="text-muted small">View only</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="10" class="text-center text-muted">No products found.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>

    {{ $products->links() }}

    <div class="modal fade" id="archivedProductsModal" tabindex="-1" aria-labelledby="archivedProductsLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="archivedProductsLabel">Archived Products</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead><tr><th>SKU</th><th>Product</th><th>Category</th><th>Archived</th>@if(auth()->user()->isOwner())<th></th>@endif</tr></thead>
                            <tbody>
                                @forelse($archivedProducts as $archivedProduct)
                                    <tr>
                                        <td>{{ $archivedProduct->sku }}</td>
                                        <td>{{ $archivedProduct->productLabel() }}</td>
                                        <td>{{ $archivedProduct->displayCategory() }}</td>
                                        <td>{{ $archivedProduct->archived_at->format('M d, Y') }}</td>
                                        @if(auth()->user()->isOwner())
                                            <td>
                                                <form action="{{ route('products.restore', $archivedProduct) }}" method="POST" onsubmit="return confirm('Restore this product to active inventory?')">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-primary">Restore</button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">No archived products.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
