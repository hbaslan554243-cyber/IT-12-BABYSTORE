<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Landing page — one screen covering every major process in the
     * system (Inventory, Sales, Restocking, Reporting), with every KPI
     * card clickable straight through to the page that explains it.
     *
     * The Revenue KPI is filterable right on the dashboard (Week / Month /
     * Year, via ?period=), so the owner gets a quick read without leaving
     * the page — clicking the card itself opens the full Revenue Reports
     * page (with charts, payment breakdown, top products) for that same
     * period.
     */
    public function index(Request $request)
    {
        $period = in_array($request->get('period'), ['week', 'month', 'year'])
            ? $request->get('period')
            : 'week';

        [$start, $end, $periodLabel] = $this->periodRange($period);

        $periodRevenue = Sale::whereBetween('created_at', [$start, $end])->sum('total_amount');
        $periodTransactions = Sale::whereBetween('created_at', [$start, $end])->count();

        $todaySalesTotal = Sale::whereDate('created_at', today())->sum('total_amount');
        $todaySalesCount = Sale::whereDate('created_at', today())->count();

        $totalProducts = Product::count();
        $lowStockCount = Product::lowStock()->count();
        $outOfStockCount = Product::outOfStock()->count();

        $lowStockProducts = Product::lowStock()->orderBy('quantity_on_hand')->limit(8)->get();
        $outOfStockProducts = Product::outOfStock()->orderBy('name')->limit(8)->get();

        $pendingPurchaseOrders = PurchaseOrder::with('supplier')
            ->where('status', 'pending')
            ->orderBy('order_date')
            ->get();

        $topProducts = DB::table('sale_items')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->select('products.name', DB::raw('SUM(sale_items.quantity) as total_qty'))
            ->groupBy('products.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'period',
            'periodLabel',
            'periodRevenue',
            'periodTransactions',
            'todaySalesTotal',
            'todaySalesCount',
            'totalProducts',
            'lowStockCount',
            'outOfStockCount',
            'lowStockProducts',
            'outOfStockProducts',
            'pendingPurchaseOrders',
            'topProducts'
        ));
    }

    /**
     * Returns [startDate, endDate, humanLabel] for the requested period,
     * always anchored to "now" (the dashboard shows the current period —
     * stepping through past periods is what the full Reports page is for).
     */
    private function periodRange(string $period): array
    {
        $now = now();

        return match ($period) {
            'month' => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
                $now->format('F Y'),
            ],
            'year' => [
                $now->copy()->startOfYear(),
                $now->copy()->endOfYear(),
                $now->format('Y'),
            ],
            default => [
                $now->copy()->startOfWeek(),
                $now->copy()->endOfWeek(),
                $now->copy()->startOfWeek()->format('M d') . ' – ' . $now->copy()->endOfWeek()->format('M d, Y'),
            ],
        };
    }
}