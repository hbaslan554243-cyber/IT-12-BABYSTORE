<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Landing page — one screen covering every major process in the
     * system (Inventory, Sales, Restocking, Reporting), with every KPI
     * card clickable straight through to the page that explains it.
     *
     * The Revenue KPI shows the all-time total and transaction count. Owners
     * can open the full Revenue Reports page for period-based analysis.
     */
    public function index()
    {
        $totalRevenue = Sale::sum('total_amount');
        $totalTransactions = Sale::count();

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
            'totalRevenue',
            'totalTransactions',
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
}
