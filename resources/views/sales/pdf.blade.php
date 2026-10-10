<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 28px 24px; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #33475B; }
    h1 { font-size: 17px; margin: 0 0 2px; }
    .subtitle { color: #83A0B4; font-size: 9px; margin-bottom: 10px; }
    .meta { margin-bottom: 10px; font-size: 9px; color: #33475B; line-height: 1.5; }
    table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    th, td { border: 1px solid #DFF0F8; padding: 4px 3px; vertical-align: middle; }
    th { background: #6EC1E4; color: #fff; font-size: 8px; text-align: center; }
    tbody tr:nth-child(even) { background: #EAF6FC; }
    td { font-size: 8px; }
    .number { text-align: right; white-space: nowrap; }
    .center { text-align: center; }
    .summary-row td { background: #CDEBF7; color: #33475B; font-weight: bold; }
    .empty { text-align: center; padding: 12px; }
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
        @if($filters['receipt']) Receipt filter: {{ $filters['receipt'] }}<br> @endif
        Transactions: {{ $sales->count() }} &nbsp; | &nbsp; Items sold: {{ $totalItemsSold }} &nbsp; | &nbsp; Sales total: P {{ number_format($grandTotal, 2) }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%">RECEIPT #</th>
                <th style="width: 13%">DATE</th>
                <th style="width: 23%">ITEM NAME</th>
                <th style="width: 8%">QTY</th>
                <th style="width: 13%">UNIT PRICE</th>
                <th style="width: 13%">AMOUNT</th>
                <th style="width: 10%">PAYMENT</th>
                <th style="width: 10%">CASHIER</th>
            </tr>
        </thead>
        <tbody>
            @php $itemCount = 0; @endphp
            @forelse($sales as $sale)
                @foreach($sale->items as $item)
                    @php $itemCount += (int) $item->quantity; @endphp
                    <tr>
                        <td>{{ $sale->sale_number }}</td>
                        <td>{{ $sale->created_at->format('M d, Y h:i A') }}</td>
                        <td>{{ $item->product->displayName() ?? '(deleted product)' }}</td>
                        <td class="center">{{ $item->quantity }}</td>
                        <td class="number">P {{ number_format($item->unit_price, 2) }}</td>
                        <td class="number">P {{ number_format($item->subtotal, 2) }}</td>
                        <td class="center">{{ $sale->payment_method === 'gcash' ? 'GCash' : 'Cash' }}</td>
                        <td>{{ $sale->cashier_name }}</td>
                    </tr>
                @endforeach
            @empty
                <tr><td colspan="8" class="empty">No sales match the selected filters.</td></tr>
            @endforelse
            @if($sales->isNotEmpty())
                <tr class="summary-row">
                    <td colspan="3">REPORT TOTAL ({{ $sales->count() }} transaction{{ $sales->count() === 1 ? '' : 's' }})</td>
                    <td class="center">{{ $itemCount }}</td>
                    <td></td>
                    <td class="number">P {{ number_format($grandTotal, 2) }}</td>
                    <td colspan="2"></td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
