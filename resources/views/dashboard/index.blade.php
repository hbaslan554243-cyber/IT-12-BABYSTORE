@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
    <h2 class="mb-4">Dashboard</h2>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card stat-card p-3">
                <div class="text-muted small">Today's Sales</div>
                <div class="fs-3 fw-bold">₱{{ number_format($todaySalesTotal, 2) }}</div>
                <div class="text-muted small">{{ $todaySalesCount }} transaction(s)</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card p-3" style="border-left-color:#e8590c">
                <div class="text-muted small">Low Stock Items</div>
                <div class="fs-3 fw-bold">{{ $lowStockProducts->count() }}</div>
                <div class="text-muted small">at or below reorder point</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card p-3" style="border-left-color:#1971c2">
                <div class="text-muted small">Pending Purchase Orders</div>
                <div class="fs-3 fw-bold">{{ $pendingPurchaseOrders->count() }}</div>
                <div class="text-muted small">awaiting delivery</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card p-3 h-100">
                <h5> Low Stock Alert <small class="text-muted">(Problem 1 fix)</small></h5>
                @if($lowStockProducts->isEmpty())
                    <p class="text-muted mb-0">All products are above their reorder point.</p>
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
                    <a href="{{ route('purchase-orders.create') }}" class="btn btn-sm btn-outline-danger">Create Purchase Order</a>
                @endif
            </div>
        </div>

        <div class="col-md-6">
            <div class="card p-3 h-100">
                <h5> Pending Deliveries <small class="text-muted">(supporting restocking module)</small></h5>
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
    </div>

    <div class="card p-3 mt-3">
        <h5> Top Selling Products</h5>
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
@endsection
