@extends('layouts.app')
@section('title', '')

@section('content')
    <h2 class="mb-4">Dashboard</h2>

    {{-- Revenue summary; optional dates filter revenue, transactions and top products. --}}
    <div class="card p-3 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
            <h5 class="mb-0">Revenue</h5>
            @if(auth()->user()->isOwner())
                <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-primary">Full Report →</a>
            @endif
        </div>

        <div class="row g-3 align-items-center">
            <div class="col-md-6">
                <div class="text-muted small">Total Revenue</div>
                <div class="fs-2 fw-bold" style="color: var(--clr-text);">₱{{ number_format($totalRevenue, 2) }}</div>
                    <form method="GET" action="{{ route('dashboard') }}" class="d-flex flex-wrap align-items-center gap-2 mt-2">
                        <div class="input-group input-group-sm" style="max-width: 210px;">
                            <span class="input-group-text">From</span>
                            <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control" aria-label="Start date">
                        </div>
                        <div class="input-group input-group-sm" style="max-width: 210px;">
                            <span class="input-group-text">To</span>
                            <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control" aria-label="End date">
                        </div>
                        <button class="btn btn-sm btn-outline-secondary">Apply</button>
                        @if($dateFrom || $dateTo)
                            <a href="{{ route('dashboard') }}" class="small text-muted">Clear</a>
                        @endif
                    </form>
            </div>
            <div class="col-md-6">
                <div class="text-muted small">Total Transactions</div>
                <div class="fs-4 fw-bold">{{ $totalTransactions }}</div>
            </div>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xxl-5 g-3 mb-4">
        <div class="col">
            <a href="{{ route('sales.log') }}" class="text-decoration-none">
                <div class="card stat-card p-3 h-100">
                    <div class="text-muted small">Today's Sales</div>
                    <div class="fs-3 fw-bold">₱{{ number_format($todaySalesTotal, 2) }}</div>
                    <div class="text-muted small">{{ $todaySalesCount }} transaction(s)</div>
                </div>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('products.index') }}" class="text-decoration-none">
                <div class="card stat-card p-3 h-100" style="border-left-color:#8FE0B4">
                    <div class="text-muted small">Total Products</div>
                    <div class="fs-3 fw-bold">{{ $totalProducts }}</div>
                </div>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('purchase-orders.index') }}" class="text-decoration-none">
                <div class="card stat-card p-3 h-100" style="border-left-color:#8FD3F4">
                    <div class="text-muted small">Pending Products</div>
                    <div class="fs-3 fw-bold">{{ $pendingPurchaseOrders->count() }}</div>
                </div>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('products.index', ['stock_filter' => 'low']) }}" class="text-decoration-none">
                <div class="card stat-card p-3 h-100" style="border-left-color:#F4C79A">
                    <div class="text-muted small">Low Stock Items</div>
                    <div class="fs-3 fw-bold">{{ $lowStockCount }}</div>
                    <div class="text-muted small">at or below reorder point</div>
                </div>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('products.index', ['stock_filter' => 'out']) }}" class="text-decoration-none">
                <div class="card stat-card p-3 h-100" style="border-left-color:#E88989">
                    <div class="text-muted small">Out of Stock</div>
                    <div class="fs-3 fw-bold">{{ $outOfStockCount }}</div>
                    <div class="text-muted small">needs restocking now</div>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card p-3 h-100">
                <h5>Low Stock <small class="text-muted"></small></h5>
                @if($lowStockProducts->isEmpty())
                    <p class="text-muted mb-0">No products are currently low on stock.</p>
                @else
                    <table class="table table-sm">
                        <thead><tr><th>Product</th><th>On Hand</th><th>Reorder Pt.</th></tr></thead>
                        <tbody>
                        @foreach($lowStockProducts as $p)
                            <tr class="low-stock">
                                <td>{{ $p->name }}</td>
                                <td>{{ $p->quantity_on_hand }}</td>
                                <td>{{ $p->reorder_point }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <a href="{{ route('products.index', ['stock_filter' => 'low']) }}" class="btn btn-sm btn-outline-secondary">View All Low Stock</a>
                @endif
            </div>
        </div>

        <div class="col-md-6">
            <div class="card p-3 h-100">
                <h5>Out of Stock</h5>
                @if($outOfStockProducts->isEmpty())
                    <p class="text-muted mb-0">Nothing is currently out of stock.</p>
                @else
                    <table class="table table-sm">
                        <thead><tr><th>Product</th><th>Category</th></tr></thead>
                        <tbody>
                        @foreach($outOfStockProducts as $p)
                            <tr class="out-of-stock-row">
                                <td>{{ $p->name }}</td>
                                <td>{{ $p->displayCategory() }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    @if(auth()->user()->isOwner())
                        <a href="{{ route('purchase-orders.create') }}" class="btn btn-sm btn-outline-danger">Create Purchase Order</a>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-md-6">
            <div class="card p-3 h-100">
                <h5>Pending Products <small class="text-muted"></small></h5>
                @if($pendingPurchaseOrders->isEmpty())
                    <p class="text-muted mb-0">No pending purchase orders.</p>
                @else
                    <table class="table table-sm">
                        <thead><tr><th>PO #</th><th>Supplier</th><th>Order Date</th><th></th></tr></thead>
                        <tbody>
                        @foreach($pendingPurchaseOrders as $po)
                            <tr>
                                <td>{{ $po->po_number }}</td>
                                <td>{{ $po->supplier->name }}</td>
                                <td>{{ $po->order_date->format('M d, Y') }}</td>
                                <td><a href="{{ route('purchase-orders.show', $po) }}">View</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <div class="col-md-6">
            <div class="card p-3 h-100">
                <h5>Top Selling Products</h5>
                @if($topProducts->isEmpty())
                    <p class="text-muted mb-0">No sales recorded yet.</p>
                @else
                    <table class="table table-sm">
                        <thead><tr><th>Product</th><th>Total Quantity Sold</th></tr></thead>
                        <tbody>
                        @foreach($topProducts as $tp)
                            <tr><td>{{ $tp->name }}</td><td>{{ $tp->total_qty }}</td></tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
@endsection
