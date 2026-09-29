@extends('layouts.app')
@section('title', 'Itemized Sales Log')

@section('content')
    <h2>Itemized Sales / Receipt Log <small class="text-muted fs-6"></small></h2>
    <p class="text-muted">Every line item ever sold, permanently recorded and traceable back to its receipt — replacing the old logbook, which kept only a single unbroken total per day.</p>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-2">
            <label class="form-label small">From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
        </div>
        <div class="col-md-2">
            <label class="form-label small">To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label small">Product</label>
            <select name="product_id" class="form-select">
                <option value="">All products</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small">Receipt #</label>
            <input type="text" name="receipt" value="{{ request('receipt') }}" class="form-control" placeholder="e.g. SO-20260923">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-outline-secondary w-100">Filter</button>
        </div>
    </form>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card p-3 stat-card">
                <div class="text-muted small">Transactions (filtered)</div>
                <div class="fs-4 fw-bold">{{ $summary->total_transactions ?? 0 }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 stat-card">
                <div class="text-muted small">Total Items Sold</div>
                <div class="fs-4 fw-bold">{{ $summary->total_qty ?? 0 }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 stat-card">
                <div class="text-muted small">Total Amount</div>
                <div class="fs-4 fw-bold">₱{{ number_format($summary->total_amount ?? 0, 2) }}</div>
            </div>
        </div>
    </div>

    <table class="table table-bordered bg-white">
        <thead>
        <tr>
            <th>Date/Time</th><th>Receipt #</th><th>Product</th><th>Qty</th><th>Unit Price</th><th>Subtotal</th><th>Cashier</th><th></th>
        </tr>
        </thead>
        <tbody>
        @forelse($items as $item)
            <tr>
                <td>{{ $item->sale->created_at->format('M d, Y h:i A') }}</td>
                <td>{{ $item->sale->sale_number }}</td>
                <td>{{ $item->product->name ?? '(deleted product)' }}</td>
                <td>{{ $item->quantity }}</td>
                <td>₱{{ number_format($item->unit_price, 2) }}</td>
                <td>₱{{ number_format($item->subtotal, 2) }}</td>
                <td>{{ $item->sale->cashier_name }}</td>
                <td><a href="{{ route('sales.show', $item->sale_id) }}" class="btn btn-sm btn-outline-primary">View Receipt</a></td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted">No itemized sales match this filter.</td></tr>
        @endforelse
        </tbody>
    </table>

    {{ $items->links() }}
@endsection
