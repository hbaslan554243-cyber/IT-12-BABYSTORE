<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #222; }
    h1 { font-size: 18px; margin: 0 0 2px 0; }
    .subtitle { color: #666; font-size: 10px; margin-bottom: 12px; }
    .meta { margin-bottom: 16px; font-size: 10px; color: #444; line-height: 1.6; }
    .sale-header {
        background: #EAF6FC;
        padding: 6px 8px;
        font-size: 10.5px;
        font-weight: bold;
        margin-top: 14px;
    }
    table.items { width: 100%; border-collapse: collapse; margin-top: 4px; }
    table.items th, table.items td {
        border: 1px solid #ccc;
        padding: 4px 6px;
        font-size: 10px;
        text-align: left;
    }
    table.items th { background: #f5f5f5; }
    .text-right { text-align: right; }
    .sale-total td { font-weight: bold; background: #fafafa; }
    .grand-total {
        margin-top: 24px;
        font-size: 13px;
        font-weight: bold;
        text-align: right;
        border-top: 2px solid #333;
        padding-top: 10px;
    }
</style>
</head>
<body>
    <h1>Aizen Baby Store</h1>
    <div class="subtitle">Barangay 23-C, Purok 5A, Boulevard, Davao City</div>

    <div class="meta">
        Sales Report &mdash; Generated {{ now()->format('M d, Y h:i A') }}<br>
        @if($filters['date_from'] || $filters['date_to'])
            Period: {{ $filters['date_from'] ?? 'Start' }} to {{ $filters['date_to'] ?? 'Present' }}<br>
        @endif
        @if($filters['receipt'])
            Receipt filter: {{ $filters['receipt'] }}<br>
        @endif
        Total Transactions: {{ $sales->count() }}
    </div>

    @forelse($sales as $sale)
        <div class="sale-header">
            Receipt #: {{ $sale->sale_number }} &nbsp;|&nbsp;
            Date/Time: {{ $sale->created_at->format('M d, Y h:i A') }} &nbsp;|&nbsp;
            Cashier: {{ $sale->cashier_name }} &nbsp;|&nbsp;
            Payment: {{ $sale->payment_method === 'gcash' ? 'GCash' : 'Cash' }}
        </div>
        <table class="items">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                    <tr>
                        <td>{{ $item->product->name ?? '(deleted product)' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td class="text-right">P {{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">P {{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="sale-total">
                    <td colspan="3" class="text-right">Total Amount:</td>
                    <td class="text-right">P {{ number_format($sale->total_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>
    @empty
        <p>No sales match the selected filters.</p>
    @endforelse

    @if($sales->isNotEmpty())
        <div class="grand-total">
            Grand Total ({{ $sales->count() }} transaction{{ $sales->count() === 1 ? '' : 's' }}): P {{ number_format($grandTotal, 2) }}
        </div>
    @endif
</body>
</html>