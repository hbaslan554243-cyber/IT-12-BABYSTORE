@extends('layouts.app')
@section('title', '')

@section('content')
    <h2>Sales &amp; Inventory Reports</h2>
    <p class="text-muted">Sales are shown for the selected period. Inventory reflects current stock.</p>

    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#sales-report" type="button" role="tab">Sales Report</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#inventory-report" type="button" role="tab">Inventory Report</button></li>
    </ul>

    <div class="tab-content">
        <section class="tab-pane fade show active" id="sales-report" role="tabpanel">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="d-flex gap-2">
                    @foreach(['week' => 'Week', 'month' => 'Month', 'year' => 'Year'] as $key => $labelName)
                        <a href="{{ route('reports.index', ['period' => $key, 'offset' => 0]) }}" class="btn {{ $period === $key ? 'btn-brand' : 'btn-outline-secondary' }} btn-sm">{{ $labelName }}</a>
                    @endforeach
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('reports.index', ['period' => $period, 'offset' => $offset - 1]) }}" class="btn btn-outline-secondary btn-sm">‹ Previous</a>
                    <span class="fw-bold px-2">{{ $label }}</span>
                    <a href="{{ route('reports.index', ['period' => $period, 'offset' => $offset + 1]) }}" class="btn btn-outline-secondary btn-sm">Next ›</a>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md"><div class="card p-3 stat-card h-100"><div class="text-muted small">Total Revenue</div><div class="fs-5 fw-bold">₱{{ number_format($totalRevenue, 2) }}</div><div class="small {{ $revenueChangePct >= 0 ? 'text-success' : 'text-danger' }}">{{ $revenueChangePct >= 0 ? '▲' : '▼' }} {{ abs($revenueChangePct) }}% vs previous {{ $period }}</div></div></div>
                <div class="col-md"><div class="card p-3 stat-card h-100" style="border-left-color:#B7A6EC"><div class="text-muted small">Transactions</div><div class="fs-5 fw-bold">{{ number_format($totalTransactions) }}</div></div></div>
                <div class="col-md"><div class="card p-3 stat-card h-100" style="border-left-color:#F4C79A"><div class="text-muted small">Items Sold</div><div class="fs-5 fw-bold">{{ number_format($totalItems) }}</div></div></div>
                <div class="col-md"><div class="card p-3 stat-card h-100" style="border-left-color:#8FE0B4"><div class="text-muted small">Average Sale</div><div class="fs-5 fw-bold">₱{{ number_format($avgSale, 2) }}</div></div></div>
                <div class="col-md"><div class="card p-3 stat-card h-100" style="border-left-color:#6EC1E4"><div class="text-muted small">Gross Profit</div><div class="fs-5 fw-bold">₱{{ number_format($grossProfit, 2) }}</div><div class="small text-muted">Revenue less cost of goods sold</div></div></div>
            </div>
            @if($estimatedCostLineCount > 0)
                <div class="alert alert-info py-2 small">Gross profit for some older sale items is estimated using today’s product cost. New sales save the cost at the time of sale.</div>
            @endif

            <div class="row g-3 mb-3">
                <div class="col-lg-8"><div class="card p-3 h-100"><h5>Revenue</h5><canvas id="revenueChart" height="120"></canvas></div></div>
                <div class="col-lg-4"><div class="card p-3 h-100"><h5>Sales by Category</h5>@if($salesByCategory->isEmpty())<p class="text-muted small mb-0">No sales in this period.</p>@else<canvas id="categoryChart" height="190"></canvas>@endif</div></div>
            </div>

            <div class="row g-3">
                <div class="col-lg-8"><div class="card p-3 h-100"><h5>Top Products</h5>
                    @if($topProducts->isEmpty())<p class="text-muted small mb-0">No sales in this period.</p>@else
                        <div class="table-responsive"><table class="table table-sm table-bordered mb-0"><thead><tr><th>Product</th><th class="text-end">Qty</th><th class="text-end">Revenue</th><th class="text-end">Profit</th></tr></thead><tbody>
                        @foreach($topProducts as $product)<tr><td>{{ $topProductModels[$product->id]->productLabel() }}</td><td class="text-end">{{ number_format($product->qty) }}</td><td class="text-end">₱{{ number_format($product->revenue, 2) }}</td><td class="text-end">₱{{ number_format($product->revenue - $product->cost_of_goods, 2) }}</td></tr>@endforeach
                        </tbody></table></div>
                    @endif
                </div></div>
                @if($paymentBreakdown->count() > 1)
                    <div class="col-lg-4"><div class="card p-3 h-100"><h5>Payment Methods</h5><canvas id="paymentChart" height="190"></canvas><div class="small mt-2">@foreach($paymentBreakdown as $payment)<div class="d-flex justify-content-between"><span>{{ $payment->payment_method === 'gcash' ? 'GCash' : 'Cash' }}</span><span>{{ $payment->count }} · ₱{{ number_format($payment->total, 2) }}</span></div>@endforeach</div></div></div>
                @endif
            </div>
        </section>

        <section class="tab-pane fade" id="inventory-report" role="tabpanel">
            <div class="small text-muted mb-3">Current stock snapshot · selected sales period: {{ $label }}</div>
            <div class="row g-3 mb-3">
                <div class="col-md"><div class="card p-3 stat-card h-100"><div class="small text-muted">Total Products</div><div class="fs-5 fw-bold">{{ number_format($inventoryProductCount) }}</div></div></div>
                <div class="col-md"><div class="card p-3 stat-card h-100" style="border-left-color:#B7A6EC"><div class="small text-muted">Units on Hand</div><div class="fs-5 fw-bold">{{ number_format($inventoryUnits) }}</div></div></div>
                <div class="col-md"><div class="card p-3 stat-card h-100" style="border-left-color:#F4C79A"><div class="small text-muted">Inventory Cost Value</div><div class="fs-5 fw-bold">₱{{ number_format($inventoryCostValue, 2) }}</div></div></div>
                <div class="col-md"><div class="card p-3 stat-card h-100" style="border-left-color:#8FE0B4"><div class="small text-muted">Potential Retail Value</div><div class="fs-5 fw-bold">₱{{ number_format($inventoryRetailValue, 2) }}</div></div></div>
                <div class="col-md"><div class="card p-3 stat-card h-100" style="border-left-color:{{ $inventoryExpectedProfit < 0 ? '#E88989' : '#6EC1E4' }}"><div class="small text-muted">Expected Profit</div><div class="fs-5 fw-bold {{ $inventoryExpectedProfit < 0 ? 'text-danger' : '' }}">₱{{ number_format($inventoryExpectedProfit, 2) }}</div><div class="small text-muted">Retail value less cost value</div></div></div>
            </div>
            @if($belowCostProductCount > 0)
                <div class="alert alert-warning py-2 small">{{ $belowCostProductCount }} product(s) have a selling price below cost. Review these prices; this can make expected inventory profit negative.
                    <div class="table-responsive mt-2"><table class="table table-sm table-bordered mb-0"><thead><tr><th>Product</th><th class="text-end">Unit Cost</th><th class="text-end">Selling Price</th><th class="text-end">Units on Hand</th></tr></thead><tbody>
                    @foreach($belowCostProducts as $product)<tr><td>{{ $product->productLabel() }} <span class="text-muted">({{ $product->sku }})</span></td><td class="text-end">₱{{ number_format($product->cost_price, 2) }}</td><td class="text-end">₱{{ number_format($product->selling_price, 2) }}</td><td class="text-end">{{ number_format($product->quantity_on_hand) }}</td></tr>@endforeach
                    </tbody></table></div>
                </div>
            @endif

            <div class="row g-3 mb-3">
                <div class="col-lg-7"><div class="card p-3 h-100"><h5>Needs Restock</h5><div class="table-responsive"><table class="table table-sm table-bordered mb-0"><thead><tr><th>SKU</th><th>Product</th><th>Status</th><th class="text-end">On Hand</th><th class="text-end">Reorder Point</th><th class="text-end">Suggested Order</th></tr></thead><tbody>
                    @forelse($needsRestock as $product)<tr><td>{{ $product->sku }}</td><td>{{ $product->productLabel() }}</td><td><span class="badge {{ $product->isOutOfStock() ? 'bg-danger' : 'bg-warning text-dark' }}">{{ $product->isOutOfStock() ? 'Out' : 'Low' }}</span></td><td class="text-end">{{ $product->quantity_on_hand }}</td><td class="text-end">{{ $product->reorder_point }}</td><td class="text-end">{{ max(1, $product->reorder_point - $product->quantity_on_hand) }}</td></tr>
                    @empty<tr><td colspan="6" class="text-center text-muted">All products are above their reorder points.</td></tr>@endforelse
                </tbody></table></div></div></div>
                <div class="col-lg-5"><div class="card p-3 h-100"><h5>Available Stock by Category</h5><small class="text-muted mb-2">Low and out of stock products are shown in Needs Restock.</small><div class="table-responsive"><table class="table table-sm table-bordered mb-0"><thead><tr><th>Category</th><th class="text-end">Products</th><th class="text-end">Units</th><th class="text-end">Cost Value</th></tr></thead><tbody>
                    @forelse($inventoryByCategory as $category)<tr><td>{{ $category['category'] }}</td><td class="text-end">{{ $category['products'] }}</td><td class="text-end">{{ number_format($category['units']) }}</td><td class="text-end">₱{{ number_format($category['cost_value'], 2) }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted">No inventory recorded.</td></tr>@endforelse
                </tbody></table></div></div></div>
            </div>

            <div class="card p-3 mb-3"><h5>Stock Movement · {{ $label }}</h5><div class="row g-3"><div class="col-md-6"><div class="rounded p-3" style="background:var(--clr-bg)"><div class="small text-muted">Units Received</div><strong>{{ number_format($unitsReceived) }}</strong></div></div><div class="col-md-6"><div class="rounded p-3" style="background:var(--clr-bg)"><div class="small text-muted">Units Sold</div><strong>{{ number_format($unitsSold) }}</strong></div></div></div></div>

            <div class="row g-3 mb-3">
                <div class="col-lg-6"><div class="card p-3 h-100"><h5>Slow / No Sales</h5><div class="table-responsive"><table class="table table-sm table-bordered mb-0"><thead><tr><th>Product</th><th class="text-end">Units Sold</th><th class="text-end">On Hand</th></tr></thead><tbody>@forelse($slowMovingProducts as $product)<tr><td>{{ $product->productLabel() }}</td><td class="text-end">{{ number_format($product->period_qty_sold) }}</td><td class="text-end">{{ $product->quantity_on_hand }}</td></tr>@empty<tr><td colspan="3" class="text-center text-muted">No products in inventory.</td></tr>@endforelse</tbody></table></div></div></div>
                <div class="col-lg-6"><div class="card p-3 h-100"><h5>Expiring Soon <small class="text-muted">(next 30 days)</small></h5><div class="table-responsive"><table class="table table-sm table-bordered mb-0"><thead><tr><th>SKU</th><th>Product</th><th>On Hand</th><th>Expiration Date</th></tr></thead><tbody>@forelse($expiringSoonProducts as $product)<tr><td>{{ $product->sku }}</td><td>{{ $product->productLabel() }}</td><td>{{ $product->quantity_on_hand }}</td><td>{{ $product->expiration_date->format('M d, Y') }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted">No stocked products expire in the next 30 days.</td></tr>@endforelse</tbody></table></div></div></div>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
<script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
<script>
    const chartColors = ['#6EC1E4', '#8FE0B4', '#B7A6EC', '#F4C79A', '#E88989', '#83A0B4', '#55B0D6'];
    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: { labels: @json($chartLabels), datasets: [{ label: 'Revenue (₱)', data: @json($chartValues), backgroundColor: '#8FD3F4', borderColor: '#55B0D6', borderWidth: 1, borderRadius: 5 }] },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { callback: (v) => '₱' + v.toLocaleString() } } } }
    });
    @if($salesByCategory->isNotEmpty())
    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: { labels: @json($salesByCategory->pluck('category')), datasets: [{ data: @json($salesByCategory->pluck('revenue')), backgroundColor: chartColors, borderColor: '#fff', borderWidth: 2 }] },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
    @endif
    @if($paymentBreakdown->count() > 1)
    new Chart(document.getElementById('paymentChart'), {
        type: 'doughnut',
        data: { labels: @json($paymentBreakdown->map(fn ($row) => $row->payment_method === 'gcash' ? 'GCash' : 'Cash')), datasets: [{ data: @json($paymentBreakdown->pluck('total')), backgroundColor: ['#6EC1E4', '#B7A6EC'], borderColor: '#fff', borderWidth: 2 }] },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
    @endif
</script>
@endsection
