<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SaleItem;
use Illuminate\Http\Request;

class SalesLogController extends Controller
{
    /**
     * Itemized Sales / Receipt Log — solves Problem 3 (Lack of Itemized
     * Sales Records). The old system's logbook recorded only a single
     * daily total with no items, quantities, or receipts retained. This
     * module keeps a permanent, line-by-line, searchable record of every
     * item sold — tied back to its receipt — so any sale can be traced,
     * verified, or reconciled against inventory at any time.
     */
    public function index(Request $request)
    {
        $applyFilters = function ($query) use ($request) {
            if ($request->filled('date_from')) {
                $query->whereDate('sales.created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('sales.created_at', '<=', $request->date_to);
            }
            if ($request->filled('product_id')) {
                $query->where('sale_items.product_id', $request->product_id);
            }
            if ($request->filled('receipt')) {
                $query->where('sales.sale_number', 'like', '%' . $request->receipt . '%');
            }
            return $query;
        };

        $listQuery = SaleItem::with(['sale', 'product'])
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->select('sale_items.*');
        $applyFilters($listQuery);

        $items = $listQuery->orderByDesc('sales.created_at')->paginate(25)->withQueryString();

        // Running totals for the currently filtered result set, so the
        // owner can verify a period's itemized sales against the
        // logbook-era single daily total, transaction by transaction.
        // Built as a fresh query (not cloned from $listQuery) so the
        // aggregate SELECT never mixes with the row-level sale_items.* columns.
        $summaryQuery = SaleItem::join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->selectRaw('SUM(sale_items.quantity) as total_qty, SUM(sale_items.subtotal) as total_amount, COUNT(DISTINCT sale_items.sale_id) as total_transactions');
        $applyFilters($summaryQuery);
        $summary = $summaryQuery->first();

        $products = Product::orderBy('name')->get();

        return view('sales.log', compact('items', 'products', 'summary'));
    }
}
