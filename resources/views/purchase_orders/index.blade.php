@extends('layouts.app')
@section('title', '')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Purchase Orders <small class="text-muted fs-6"></small></h2>
        @if(auth()->user()->isOwner())
            <a href="{{ route('purchase-orders.create') }}" class="btn btn-danger">+ New Purchase Order</a>
        @endif
    </div>

    <table class="table table-bordered bg-white">
        <thead><tr><th>PO #</th><th>Supplier</th><th>Order Date</th><th>Expected</th><th>Status</th><th>Total</th><th></th></tr></thead>
        <tbody>
        @forelse($purchaseOrders as $po)
            <tr>
                <td>{{ $po->po_number }}</td>
                <td>{{ $po->supplier->name }}</td>
                <td>{{ $po->order_date->format('M d, Y') }}</td>
                <td>{{ $po->expected_delivery_date?->format('M d, Y') ?? '—' }}</td>
                <td>
                    @if($po->status === 'delivered')
                        <span class="badge bg-success">Delivered</span>
                    @elseif($po->status === 'pending')
                        <span class="badge bg-warning text-dark">Pending</span>
                    @else
                        <span class="badge bg-secondary">Cancelled</span>
                    @endif
                </td>
                <td>₱{{ number_format($po->total_amount, 2) }}</td>
                <td><a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-sm btn-outline-primary">View</a></td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted">No purchase orders recorded yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $purchaseOrders->links() }}
@endsection
