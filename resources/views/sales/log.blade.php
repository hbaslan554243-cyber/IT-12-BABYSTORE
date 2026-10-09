@extends('layouts.app')
@section('title', 'Sales Records')

@section('content')
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <h2>Sales Records <small class="text-muted fs-6">(Problems 2 &amp; 3 combined)</small></h2>
            <p class="text-muted">Every sale, with its exact items one click away — click any row to expand it, or "View Receipt" for a quick preview.</p>
        </div>
        <a href="{{ route('sales.log.pdf', request()->query()) }}" target="_blank" class="btn btn-outline-secondary">📄 Export PDF</a>
    </div>
    
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
            <input type="text" name="receipt" value="{{ request('receipt') }}" class="form-control" placeholder="e.g. SO-000123">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-outline-secondary w-100">Filter</button>
        </div>
    </form>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card p-3 stat-card">
                <div class="text-muted small">Transactions (filtered)</div>
                <div class="fs-4 fw-bold">{{ $totalTransactions }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 stat-card">
                <div class="text-muted small">Total Items Sold</div>
                <div class="fs-4 fw-bold">{{ $totalItemsSold }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 stat-card">
                <div class="text-muted small">Total Amount</div>
                <div class="fs-4 fw-bold">₱{{ number_format($totalAmount, 2) }}</div>
            </div>
        </div>
    </div>

    <table class="table table-bordered bg-white">
        <thead>
        <tr>
            <th style="width:30px"></th>
            <th>Date/Time</th><th>Receipt #</th><th>Cashier</th><th>Items</th><th>Payment</th><th>Total</th><th></th>
        </tr>
        </thead>
        <tbody>
        @forelse($sales as $sale)
            <tr class="sale-row" data-bs-toggle="collapse" data-bs-target="#items-{{ $sale->id }}" aria-expanded="false" style="cursor:pointer;">
                <td class="text-center"><span class="chevron">▸</span></td>
                <td>{{ $sale->created_at->format('M d, Y h:i A') }}</td>
                <td>{{ $sale->sale_number }}</td>
                <td>{{ $sale->cashier_name }}</td>
                <td>{{ $sale->items_count }} item(s)</td>
                <td>{{ $sale->payment_method === 'gcash' ? '📱 GCash' : '💵 Cash' }}</td>
                <td>₱{{ number_format($sale->total_amount, 2) }}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-primary view-receipt-btn" data-bs-toggle="modal" data-bs-target="#receiptModal-{{ $sale->id }}">View Receipt</button>
                </td>
            </tr>
            <tr class="collapse" id="items-{{ $sale->id }}">
                <td colspan="8" class="p-0 border-0">
                    <div class="p-3" style="background: var(--clr-bg);">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Subtotal</th></tr></thead>
                            <tbody>
                            @foreach($sale->items as $item)
                                <tr>
                                    <td>{{ $item->product->name ?? '(deleted product)' }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>₱{{ number_format($item->unit_price, 2) }}</td>
                                    <td>₱{{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted">No sales match this filter.</td></tr>
        @endforelse
        </tbody>
    </table>

    {{ $sales->links() }}

    {{-- Receipt preview modals — kept outside the table (a modal inside a
         <tr>/<td> is invalid table markup even though browsers tolerate it). --}}
    @foreach($sales as $sale)
        <div class="modal fade" id="receiptModal-{{ $sale->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 20px; border: none;">
                    <div class="modal-body p-4">
                        <div class="text-center mb-3">
                            <h4 class="fw-bold mb-1">Aizen Baby Store</h4>
                            <div class="text-muted small">Barangay 23-C, Purok 5A, Boulevard, Davao City</div>
                            <hr>
                            <div>Receipt #: <strong>{{ $sale->sale_number }}</strong></div>
                            <div>Date: {{ $sale->created_at->format('M d, Y h:i A') }}</div>
                            <div>Cashier: {{ $sale->cashier_name }}</div>
                        </div>
                        <table class="table table-sm">
                            <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
                            <tbody>
                            @foreach($sale->items as $item)
                                <tr>
                                    <td>{{ $item->product->name ?? '(deleted product)' }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>₱{{ number_format($item->unit_price, 2) }}</td>
                                    <td>₱{{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <hr>
                        <div class="d-flex justify-content-between fs-5 fw-bold">
                            <span>TOTAL</span><span>₱{{ number_format($sale->total_amount, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Payment Method</span>
                            <span>
                                @if($sale->payment_method === 'gcash')
                                    📱 GCash @if($sale->gcash_reference)(Ref: {{ $sale->gcash_reference }})@endif
                                @else
                                    💵 Cash
                                @endif
                            </span>
                        </div>
                        @if($sale->payment_method === 'gcash')
                            <div class="d-flex justify-content-between">
                                <span>Amount Paid</span><span>₱{{ number_format($sale->total_amount, 2) }}</span>
                            </div>
                        @else
                            <div class="d-flex justify-content-between">
                                <span>Cash Received</span><span>₱{{ number_format($sale->cash_received, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Change</span><span>₱{{ number_format($sale->change_due, 2) }}</span>
                            </div>
                        @endif
                        <hr>
                        <p class="text-center text-muted small mb-3">Thank you for shopping at Aizen Baby Store!</p>

                        <div class="d-flex gap-2">
                            <a href="{{ route('sales.show', $sale) }}" class="btn btn-outline-secondary flex-fill" target="_blank">Open Full Receipt / Print</a>
                            <button type="button" class="btn btn-brand flex-fill" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@section('scripts')
<style>
    .sale-row[aria-expanded="true"] .chevron { transform: rotate(90deg); display: inline-block; }
    .chevron { transition: transform .15s ease; display: inline-block; color: var(--clr-text-muted); }
</style>
<script>
    // Prevent "View Receipt" from also toggling the row's expand/collapse,
    // since the button sits inside a row that has its own click handler.
    document.querySelectorAll('.view-receipt-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    });
</script>
@endsection