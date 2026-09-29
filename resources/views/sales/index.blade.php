@extends('layouts.app')
@section('title', 'Sales History')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Sales History <small class="text-muted fs-6"></small></h2>
        <a href="{{ route('sales.create') }}" class="btn btn-danger">+ New Sale</a>
    </div>

    <table class="table table-bordered bg-white">
        <thead><tr><th>Sale #</th><th>Date</th><th>Cashier</th><th>Items</th><th>Payment</th><th>Total</th><th></th></tr></thead>
        <tbody>
        @forelse($sales as $sale)
            <tr>
                <td>{{ $sale->sale_number }}</td>
                <td>{{ $sale->created_at->format('M d, Y h:i A') }}</td>
                <td>{{ $sale->cashier_name }}</td>
                <td>{{ $sale->items_count }}</td>
                <td>{{ $sale->payment_method === 'gcash' ? ' GCash' : ' Cash' }}</td>
                <td>₱{{ number_format($sale->total_amount, 2) }}</td>
                <td><a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-outline-primary">View Receipt</a></td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted">No sales recorded yet.</td></tr>
        @endforelse
        </tbody>
    </table>

    {{ $sales->links() }}
@endsection
