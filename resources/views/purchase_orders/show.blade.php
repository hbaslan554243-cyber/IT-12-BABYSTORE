@extends('layouts.app')
@section('title', 'PO ' . $purchaseOrder->po_number)

@section('content')
    <h2>Purchase Order: {{ $purchaseOrder->po_number }}</h2>

    <div class="card p-4 mb-3">
        <div class="row">
            <div class="col-md-4"><strong>Supplier:</strong> {{ $purchaseOrder->supplier->name }}</div>
            <div class="col-md-4"><strong>Order Date:</strong> {{ $purchaseOrder->order_date->format('M d, Y') }}</div>
            <div class="col-md-4"><strong>Expected Delivery:</strong> {{ $purchaseOrder->expected_delivery_date?->format('M d, Y') ?? '—' }}</div>
        </div>
        @if($purchaseOrder->notes)
            <div class="mt-2"><strong>Notes:</strong> {{ $purchaseOrder->notes }}</div>
        @endif
        <div class="mt-2">
            <strong>Status:</strong>
            @if($purchaseOrder->status === 'delivered')
                <span class="badge bg-success">Delivered on {{ $purchaseOrder->delivered_date?->format('M d, Y') }}</span>
            @else
                <span class="badge bg-warning text-dark">Pending</span>
            @endif
        </div>
    </div>

    <div class="card p-4">
        <h5>Items</h5>
        <form method="POST" action="{{ route('purchase-orders.deliver', $purchaseOrder) }}">
            @csrf
            <table class="table">
                <thead>
                <tr><th>Product</th><th>Qty Ordered</th><th>Unit Cost</th><th>Subtotal</th>
                    @if($purchaseOrder->status === 'pending')<th>Qty Delivered</th>@else<th>Qty Delivered</th>@endif
                </tr>
                </thead>
                <tbody>
                @foreach($purchaseOrder->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity_ordered }}</td>
                        <td>₱{{ number_format($item->unit_cost, 2) }}</td>
                        <td>₱{{ number_format($item->subtotal, 2) }}</td>
                        <td>
                            @if($purchaseOrder->status === 'pending')
                                <input type="number" name="delivered[{{ $item->id }}]" class="form-control" min="0" max="{{ $item->quantity_ordered }}" value="{{ $item->quantity_ordered }}">
                            @else
                                {{ $item->quantity_delivered }}
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-between fs-5 fw-bold">
                <span>TOTAL</span><span>₱{{ number_format($purchaseOrder->total_amount, 2) }}</span>
            </div>

            @if($purchaseOrder->status === 'pending')
                <button class="btn btn-success mt-3">Confirm Delivery &amp; Update Stock</button>
                <div class="form-text">Confirming delivery automatically adds the delivered quantities to inventory (Problem 1 link).</div>
            @endif
        </form>
    </div>
@endsection
