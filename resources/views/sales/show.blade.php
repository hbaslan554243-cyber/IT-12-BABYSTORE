@extends('layouts.app')
@section('title', 'Receipt ' . $sale->sale_number)

@section('content')
    <div class="card p-4 mx-auto" style="max-width: 500px;">
        <div class="text-center mb-3">
            <h4>Aizen Baby Store</h4>
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
                    <td>{{ $item->product->name }}</td>
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
        <div class="d-flex justify-content-between">
            <span>Cash Received</span><span>₱{{ number_format($sale->cash_received, 2) }}</span>
        </div>
        <div class="d-flex justify-content-between">
            <span>Change</span><span>₱{{ number_format($sale->change_due, 2) }}</span>
        </div>
        <hr>
        <p class="text-center text-muted small mb-0">Thank you for shopping at Aizen Baby Store!</p>
    </div>

    <div class="text-center mt-3">
        <a href="{{ route('sales.create') }}" class="btn btn-danger">New Sale</a>
        <button onclick="window.print()" class="btn btn-outline-secondary">Print Receipt</button>
    </div>
@endsection
