@extends('layouts.app')
@section('title', 'Receipt ' . $sale->sale_number)

@section('content')
    <div class="card p-4 mx-auto receipt-card" style="max-width: 500px;">
        <div class="text-center mb-3">
            <h4>Aizen Baby Store</h4>
            <div class="text-muted small">Barangay 23-C, Purok 5A, Boulevard, Davao City</div>
            <hr>
            <div>Receipt #: <strong>{{ $sale->sale_number }}</strong></div>
            <div>Date: {{ $sale->created_at->format('M d, Y h:i A') }}</div>
            <div>Cashier: {{ $sale->cashier_name }}@if($sale->user) <span class="text-muted">({{ ucfirst($sale->user->role) }} account)</span>@endif</div>
        </div>
        <table class="table table-sm">
            <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
            <tbody>
            @foreach($sale->items as $item)
                <tr>
                    <td>{{ $item->product ? $item->product->productLabel() : '(deleted product)' }}</td>
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
                    GCash @if($sale->gcash_reference)(Ref: {{ $sale->gcash_reference }})@endif
                @else
                    Cash
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
        <p class="text-center text-muted small mb-0">Thank you for shopping at Aizen Baby Store!</p>
    </div>

    <div class="text-center mt-3 no-print">
        <a href="{{ route('sales.create') }}" class="btn btn-danger">New Sale</a>
        <button onclick="window.print()" class="btn btn-outline-secondary">Print Receipt</button>
    </div>
@endsection

@section('scripts')
<style>
    /* Print styles — without these, "Print Receipt" would print the
       sidebar, topbar, and buttons along with it. This hides everything
       except the receipt itself and narrows it to a realistic paper-receipt
       width instead of the on-screen dashboard card width. */
    @media print {
        .sidebar, .topbar, .no-print { display: none !important; }
        .main-area { margin-left: 0 !important; }
        .content-wrap { padding: 0 !important; }
        body { background: #fff !important; }
        .receipt-card {
            box-shadow: none !important;
            border: none !important;
            max-width: 320px !important;
            font-family: 'Courier New', monospace;
            font-size: 12px;
        }
        .receipt-card h4 { font-size: 15px; }
        .receipt-card table { font-size: 11.5px; }
    }
</style>
@endsection
