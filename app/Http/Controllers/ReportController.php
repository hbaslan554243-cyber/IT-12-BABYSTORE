<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
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
        $costOfGoodsSold = (float) DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->whereBetween('sales.created_at', [$start, $end])
            ->sum(DB::raw('sale_items.quantity * COALESCE(sale_items.unit_cost_at_sale, products.cost_price)'));
        $grossProfit = $totalRevenue - $costOfGoodsSold;
        $estimatedCostLineCount = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereBetween('sales.created_at', [$start, $end])
            ->whereNull('sale_items.unit_cost_at_sale')
            ->count();
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
            ->select('products.id', 'products.name', DB::raw('SUM(sale_items.quantity) as qty'), DB::raw('SUM(sale_items.subtotal) as revenue'), DB::raw('SUM(sale_items.quantity * COALESCE(sale_items.unit_cost_at_sale, products.cost_price)) as cost_of_goods'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();
        $topProductModels = Product::whereIn('id', $topProducts->pluck('id'))->get()->keyBy('id');

        $reportCategory = "CASE WHEN products.category IN ('Clothing', 'Other', 'Others') THEN 'Uncategorized' ELSE COALESCE(products.category, 'Uncategorized') END";
        $salesByCategory = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->whereBetween('sales.created_at', [$start, $end])
            ->select(DB::raw($reportCategory . ' as category'), DB::raw('SUM(sale_items.subtotal) as revenue'))
            ->groupByRaw($reportCategory)
            ->orderByDesc('revenue')
            ->get();

        // --- Current inventory snapshot (independent of the sales period) ---
        $inventoryProducts = Product::active()->orderBy('name')->get();
        $inventoryProductCount = $inventoryProducts->count();
        $inventoryUnits = (int) $inventoryProducts->sum('quantity_on_hand');
        $inventoryCostValue = (float) $inventoryProducts->sum(fn ($product) => $product->cost_price * $product->quantity_on_hand);
        $inventoryRetailValue = (float) $inventoryProducts->sum(fn ($product) => $product->selling_price * $product->quantity_on_hand);
        $inventoryExpectedProfit = $inventoryRetailValue - $inventoryCostValue;
        $needsRestock = $inventoryProducts->filter(fn ($product) => $product->isLowStock() || $product->isOutOfStock())
            ->sortBy('quantity_on_hand')->values();
        $belowCostProducts = $inventoryProducts->filter(fn ($product) => $product->selling_price < $product->cost_price)->values();
        $belowCostProductCount = $belowCostProducts->count();
        $expiringSoonProducts = $inventoryProducts->filter(fn ($product) => $product->quantity_on_hand > 0 && $product->isExpiringSoon(30))->sortBy('expiration_date')->values();

        $movementQuery = DB::table('inventory_movements')->whereBetween('occurred_at', [$start, $end]);
        $unitsReceived = (int) (clone $movementQuery)->where('type', 'receipt')->sum('quantity_delta');
        $unitsSold = (int) abs((clone $movementQuery)->where('type', 'sale')->sum('quantity_delta'));
        $soldQuantities = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereBetween('sales.created_at', [$start, $end])
            ->select('sale_items.product_id', DB::raw('SUM(sale_items.quantity) as qty'))
            ->groupBy('sale_items.product_id')
            ->pluck('qty', 'product_id');
        $movementProducts = $inventoryProducts->filter(fn ($product) => $product->quantity_on_hand > 0)->map(function ($product) use ($soldQuantities) {
            $product->period_qty_sold = (int) ($soldQuantities[$product->id] ?? 0);
            return $product;
        });
        $slowMovingProducts = $movementProducts->sortBy('period_qty_sold')->take(5)->values();
        $categoryStockProducts = $inventoryProducts->filter(fn ($product) => !$product->isLowStock() && !$product->isOutOfStock());
        $inventoryByCategory = $categoryStockProducts->groupBy(fn ($product) => in_array($product->category, Product::REMOVED_CATEGORIES, true) ? null : $product->category)->map(fn ($products, $category) => [
            'category' => $category ?: 'Uncategorized',
            'products' => $products->count(),
            'units' => (int) $products->sum('quantity_on_hand'),
            'cost_value' => (float) $products->sum(fn ($product) => $product->cost_price * $product->quantity_on_hand),
        ])->sortBy('category')->values();

        // --- Chart data: revenue trend across the period ---
        [$chartLabels, $chartValues] = $period === 'year'
            ? $this->monthlyBuckets($start, $end)
            : $this->dailyBuckets($start, $end);

        return view('reports.index', compact(
            'period', 'offset', 'label', 'start', 'end',
            'totalRevenue', 'totalTransactions', 'totalItems', 'avgSale',
            'costOfGoodsSold', 'grossProfit', 'estimatedCostLineCount',
            'revenueChangePct', 'paymentBreakdown', 'topProducts',
            'topProductModels',
            'chartLabels', 'chartValues', 'salesByCategory',
            'inventoryProducts', 'inventoryProductCount', 'inventoryUnits',
            'inventoryCostValue', 'inventoryRetailValue', 'inventoryExpectedProfit',
            'belowCostProductCount', 'belowCostProducts', 'needsRestock', 'expiringSoonProducts',
            'inventoryByCategory', 'unitsReceived', 'unitsSold', 'slowMovingProducts'
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
        $monthExpression = match (DB::connection()->getDriverName()) {
            'pgsql' => 'CAST(EXTRACT(MONTH FROM created_at) AS INTEGER)',
            'sqlite' => "CAST(strftime('%m', created_at) AS INTEGER)",
            default => 'MONTH(created_at)',
        };

        $raw = Sale::whereBetween('created_at', [$start, $end])
            ->selectRaw($monthExpression.' as bucket, SUM(total_amount) as revenue')
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
