<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Landing page. Surfaces, at a glance, the three things the old
     * manual system could never show the owner:
     *  1. Today's actual sales total (computed, not memory-guessed).
     *  2. Which products are at/below reorder point (Problem 1 fix).
     *  3. Which purchase orders are still pending delivery (supporting restocking module).
     */
    public function index()
    {
        $todaySalesTotal = Sale::whereDate('created_at', today())->sum('total_amount');
        $todaySalesCount = Sale::whereDate('created_at', today())->count();

        $lowStockProducts = Product::lowStock()->orderBy('quantity_on_hand')->get();

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
            'todaySalesTotal',
            'todaySalesCount',
            'lowStockProducts',
            'pendingPurchaseOrders',
            'topProducts'
        ));
    }
}
