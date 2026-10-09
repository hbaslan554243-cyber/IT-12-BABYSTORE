@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
    <h2 class="mb-4">Dashboard</h2>

    {{-- Revenue KPI — filterable right here, clickable through to full Reports --}}
    <div class="card p-3 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
            <h5 class="mb-0">💰 Revenue</h5>
            <div class="d-flex gap-2">
                @foreach(['week' => 'Week', 'month' => 'Month', 'year' => 'Year'] as $key => $label)
                    <a href="{{ route('dashboard', ['period' => $key]) }}"
                       class="btn btn-sm {{ $period === $key ? 'btn-brand' : 'btn-outline-secondary' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>

        @if(auth()->user()->isOwner())
            <a href="{{ route('reports.index', ['period' => $period]) }}" class="text-decoration-none">
        @endif
            <div class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="text-muted small">{{ $periodLabel }}</div>
                    <div class="fs-2 fw-bold" style="color: var(--clr-text);">₱{{ number_format($periodRevenue, 2) }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Transactions</div>
                    <div class="fs-4 fw-bold">{{ $periodTransactions }}</div>
                </div>
                <div class="col-md-3 text-md-end">
                    @if(auth()->user()->isOwner())
                        <span class="btn btn-sm btn-outline-primary">View Full Report →</span>
                    @endif
                </div>
            </div>
        @if(auth()->user()->isOwner())
            </a>
        @endif
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <a href="{{ route('sales.log') }}" class="text-decoration-none">
                <div class="card stat-card p-3 h-100">
                    <div class="text-muted small">Today's Sales</div>
                    <div class="fs-3 fw-bold">₱{{ number_format($todaySalesTotal, 2) }}</div>
                    <div class="text-muted small">{{ $todaySalesCount }} transaction(s)</div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('products.index', ['stock_filter' => 'low']) }}" class="text-decoration-none">
                <div class="card stat-card p-3 h-100" style="border-left-color:#F4C79A">
                    <div class="text-muted small">Low Stock Items</div>
                    <div class="fs-3 fw-bold">{{ $lowStockCount }}</div>
                    <div class="text-muted small">at or below reorder point</div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('products.index', ['stock_filter' => 'out']) }}" class="text-decoration-none">
                <div class="card stat-card p-3 h-100" style="border-left-color:#E88989">
                    <div class="text-muted small">Out of Stock</div>
                    <div class="fs-3 fw-bold">{{ $outOfStockCount }}</div>
                    <div class="text-muted small">needs restocking now</div>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <a href="{{ route('products.index') }}" class="text-decoration-none">
                <div class="card p-3 h-100 stat-card" style="border-left-color:#8FE0B4">
                    <div class="text-muted small">Total Products</div>
                    <div class="fs-4 fw-bold">{{ $totalProducts }}</div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('purchase-orders.index') }}" class="text-decoration-none">
                <div class="card p-3 h-100 stat-card" style="border-left-color:#8FD3F4">
                    <div class="text-muted small">Pending Purchase Orders</div>
                    <div class="fs-4 fw-bold">{{ $pendingPurchaseOrders->count() }}</div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('sales.create') }}" class="text-decoration-none">
                <div class="card p-3 h-100 stat-card" style="border-left-color:#6EC1E4">
                    <div class="text-muted small">Quick Action</div>
                    <div class="fs-5 fw-bold">+ Start New Sale</div>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card p-3 h-100">
                <h5>⚠️ Low Stock Alert <small class="text-muted">(Problem 1 fix)</small></h5>
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
                <h5>🚫 Out of Stock</h5>
                @if($outOfStockProducts->isEmpty())
                    <p class="text-muted mb-0">Nothing is currently out of stock.</p>
                @else
                    <table class="table table-sm">
                        <thead><tr><th>Product</th><th>Category</th></tr></thead>
                        <tbody>
                        @foreach($outOfStockProducts as $p)
                            <tr class="out-of-stock-row">
                                <td>{{ $p->name }}</td>
                                <td>{{ $p->category }}</td>
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
                <h5>📦 Pending Deliveries <small class="text-muted">(supporting restocking module)</small></h5>
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