@extends('layouts.app')
@section('title', 'Revenue Reports')

@section('content')
    <h2>Revenue &amp; Summary Reports</h2>
    <p class="text-muted">Evidence-based sales insight — filter by week, month, or year instead of relying on memory.</p>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div class="d-flex gap-2">
            @foreach(['week' => 'Week', 'month' => 'Month', 'year' => 'Year'] as $key => $labelName)
                <a href="{{ route('reports.index', ['period' => $key, 'offset' => 0]) }}"
                   class="btn {{ $period === $key ? 'btn-brand' : 'btn-outline-secondary' }} btn-sm">{{ $labelName }}</a>
            @endforeach
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('reports.index', ['period' => $period, 'offset' => $offset - 1]) }}" class="btn btn-outline-secondary btn-sm">‹ Previous</a>
            <span class="fw-bold px-2">{{ $label }}</span>
            <a href="{{ route('reports.index', ['period' => $period, 'offset' => $offset + 1]) }}" class="btn btn-outline-secondary btn-sm">Next ›</a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card p-3 stat-card">
                <div class="text-muted small">Total Revenue</div>
                <div class="fs-4 fw-bold">₱{{ number_format($totalRevenue, 2) }}</div>
                <div class="small {{ $revenueChangePct >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $revenueChangePct >= 0 ? '▲' : '▼' }} {{ abs($revenueChangePct) }}% vs previous {{ $period }}
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 stat-card" style="border-left-color:#B7A6EC">
                <div class="text-muted small">Transactions</div>
                <div class="fs-4 fw-bold">{{ $totalTransactions }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 stat-card" style="border-left-color:#F4C79A">
                <div class="text-muted small">Items Sold</div>
                <div class="fs-4 fw-bold">{{ $totalItems }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 stat-card" style="border-left-color:#8FE0B4">
                <div class="text-muted small">Average Sale</div>
                <div class="fs-4 fw-bold">₱{{ number_format($avgSale, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-8">
            <div class="card p-3">
                <h5>Revenue Trend</h5>
                <canvas id="revenueChart" height="110"></canvas>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3 mb-3">
                <h5>Payment Method</h5>
                @if($paymentBreakdown->isEmpty())
                    <p class="text-muted small mb-0">No sales in this period.</p>
                @else
                    @foreach($paymentBreakdown as $pm)
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ $pm->payment_method === 'gcash' ? '📱 GCash' : '💵 Cash' }} ({{ $pm->count }})</span>
                            <span class="fw-bold">₱{{ number_format($pm->total, 2) }}</span>
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="card p-3">
                <h5>Top Products</h5>
                @if($topProducts->isEmpty())
                    <p class="text-muted small mb-0">No sales in this period.</p>
                @else
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Product</th><th>Qty</th><th>Revenue</th></tr></thead>
                        <tbody>
                        @foreach($topProducts as $tp)
                            <tr>
                                <td>{{ $tp->name }}</td>
                                <td>{{ $tp->qty }}</td>
                                <td>₱{{ number_format($tp->revenue, 2) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
<script>
    const ctx = document.getElementById('revenueChart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Revenue (₱)',
                data: @json($chartValues),
                borderColor: '#6EC1E4',
                backgroundColor: 'rgba(143, 211, 244, 0.2)',
                fill: true,
                tension: 0.3,
                pointRadius: 3,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: (v) => '₱' + v.toLocaleString() } }
            }
        }
    });
</script>
@endsection
