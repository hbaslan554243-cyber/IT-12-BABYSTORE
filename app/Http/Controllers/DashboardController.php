<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    public function index(Request $request)
    {
        $rules = [
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ];
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $rules['date_to'] .= '|after_or_equal:date_from';
        }
        $filters = $request->validate($rules);
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;

        $salesInRange = Sale::query();
        if ($dateFrom) {
            $salesInRange->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $salesInRange->whereDate('created_at', '<=', $dateTo);
        }
        $totalRevenue = (clone $salesInRange)->sum('total_amount');
        $totalTransactions = (clone $salesInRange)->count();

        $todaySalesTotal = Sale::whereDate('created_at', today())->sum('total_amount');
        $todaySalesCount = Sale::whereDate('created_at', today())->count();

        $totalProducts = Product::active()->count();
        $lowStockCount = Product::active()->lowStock()->count();
        $outOfStockCount = Product::active()->outOfStock()->count();

        $lowStockProducts = Product::active()->lowStock()->orderBy('quantity_on_hand')->limit(8)->get();
        $outOfStockProducts = Product::active()->outOfStock()->orderBy('name')->limit(8)->get();

        $pendingPurchaseOrders = PurchaseOrder::with('supplier')
            ->where('status', 'pending')
            ->orderBy('order_date')
            ->get();

        $topProductsQuery = DB::table('sale_items')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->select('products.name', DB::raw('SUM(sale_items.quantity) as total_qty'))
            ->groupBy('products.name')
            ->orderByDesc('total_qty');
        if ($dateFrom) {
            $topProductsQuery->whereDate('sales.created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $topProductsQuery->whereDate('sales.created_at', '<=', $dateTo);
        }
        $topProducts = $topProductsQuery->limit(5)->get();

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
            'topProducts',
            'dateFrom',
            'dateTo'
        ));
    }
}
