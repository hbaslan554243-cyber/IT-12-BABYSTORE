<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Revenue & Summary Reports — Owner-only.
     * Gives the owner the evidence-based insights Objective 3 of the paper
     * promises (stocking/marketing decisions based on data, not memory).
     * Filterable by Week, Month, or Year, with prev/next navigation between
     * periods and a comparison against the immediately preceding period.
     */
    public function index(Request $request)
    {
        $period = in_array($request->get('period'), ['week', 'month', 'year']) ? $request->get('period') : 'week';
        $offset = (int) $request->get('offset', 0);
        $now = Carbon::now();

        [$start, $end, $label, $prevStart, $prevEnd] = match ($period) {
            'month' => $this->monthRange($now, $offset),
            'year' => $this->yearRange($now, $offset),
            default => $this->weekRange($now, $offset),
        };

        // --- Summary totals for the selected period ---
        $totalRevenue = (float) Sale::whereBetween('created_at', [$start, $end])->sum('total_amount');
        $totalTransactions = Sale::whereBetween('created_at', [$start, $end])->count();
        $totalItems = (int) DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereBetween('sales.created_at', [$start, $end])
            ->sum('sale_items.quantity');
        $avgSale = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        // --- Comparison against the previous equivalent period ---
        $prevRevenue = (float) Sale::whereBetween('created_at', [$prevStart, $prevEnd])->sum('total_amount');
        $revenueChangePct = $prevRevenue > 0
            ? round((($totalRevenue - $prevRevenue) / $prevRevenue) * 100, 1)
            : ($totalRevenue > 0 ? 100.0 : 0.0);

        // --- Payment method breakdown (Cash vs GCash) ---
        $paymentBreakdown = Sale::whereBetween('created_at', [$start, $end])
            ->select('payment_method', DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        // --- Top selling products for the period ---
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->whereBetween('sales.created_at', [$start, $end])
            ->select('products.name', DB::raw('SUM(sale_items.quantity) as qty'), DB::raw('SUM(sale_items.subtotal) as revenue'))
            ->groupBy('products.name')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        // --- Chart data: revenue trend across the period ---
        [$chartLabels, $chartValues] = $period === 'year'
            ? $this->monthlyBuckets($start, $end)
            : $this->dailyBuckets($start, $end);

        return view('reports.index', compact(
            'period', 'offset', 'label', 'start', 'end',
            'totalRevenue', 'totalTransactions', 'totalItems', 'avgSale',
            'revenueChangePct', 'paymentBreakdown', 'topProducts',
            'chartLabels', 'chartValues'
        ));
    }

    private function weekRange(Carbon $now, int $offset): array
    {
        $anchor = $now->copy()->addWeeks($offset);
        $start = $anchor->copy()->startOfWeek(Carbon::MONDAY);
        $end = $anchor->copy()->endOfWeek(Carbon::SUNDAY);
        $label = $start->format('M d') . ' – ' . $end->format('M d, Y');

        $prevAnchor = $anchor->copy()->subWeek();
        $prevStart = $prevAnchor->copy()->startOfWeek(Carbon::MONDAY);
        $prevEnd = $prevAnchor->copy()->endOfWeek(Carbon::SUNDAY);

        return [$start, $end, $label, $prevStart, $prevEnd];
    }

    private function monthRange(Carbon $now, int $offset): array
    {
        $anchor = $now->copy()->addMonths($offset);
        $start = $anchor->copy()->startOfMonth();
        $end = $anchor->copy()->endOfMonth();
        $label = $anchor->format('F Y');

        $prevAnchor = $anchor->copy()->subMonth();
        $prevStart = $prevAnchor->copy()->startOfMonth();
        $prevEnd = $prevAnchor->copy()->endOfMonth();

        return [$start, $end, $label, $prevStart, $prevEnd];
    }

    private function yearRange(Carbon $now, int $offset): array
    {
        $anchor = $now->copy()->addYears($offset);
        $start = $anchor->copy()->startOfYear();
        $end = $anchor->copy()->endOfYear();
        $label = $anchor->format('Y');

        $prevAnchor = $anchor->copy()->subYear();
        $prevStart = $prevAnchor->copy()->startOfYear();
        $prevEnd = $prevAnchor->copy()->endOfYear();

        return [$start, $end, $label, $prevStart, $prevEnd];
    }

    /** One data point per day — used for Week and Month views. */
    private function dailyBuckets(Carbon $start, Carbon $end): array
    {
        $raw = Sale::whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as bucket, SUM(total_amount) as revenue')
            ->groupBy('bucket')
            ->pluck('revenue', 'bucket');

        $labels = [];
        $values = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m-d');
            $labels[] = $cursor->format('M d');
            $values[] = (float) ($raw[$key] ?? 0);
            $cursor->addDay();
        }

        return [$labels, $values];
    }

    /** One data point per month — used for the Year view. */
    private function monthlyBuckets(Carbon $start, Carbon $end): array
    {
        $raw = Sale::whereBetween('created_at', [$start, $end])
            ->selectRaw('MONTH(created_at) as bucket, SUM(total_amount) as revenue')
            ->groupBy('bucket')
            ->pluck('revenue', 'bucket');

        $labels = [];
        $values = [];
        for ($m = 1; $m <= 12; $m++) {
            $labels[] = Carbon::create()->month($m)->format('M');
            $values[] = (float) ($raw[$m] ?? 0);
        }

        return [$labels, $values];
    }
}
