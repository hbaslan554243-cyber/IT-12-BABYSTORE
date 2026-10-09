<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesLogController extends Controller
{
    /**
     * Sales Records — one unified, transaction-grouped list. Each sale is
     * a single row (receipt #, cashier, payment, total); its line items
     * expand underneath it on click, or open as a quick receipt-preview
     * modal. Still solves both: Problem 2 (itemized detail is one click
     * away) and Problem 3 (every sale remains traceable to its exact
     * items).
     */
    public function index(Request $request)
    {
        $listQuery = Sale::with(['items.product'])->withCount('items');
        $this->applySaleFilters($listQuery, $request);
        $sales = $listQuery->latest()->paginate(20)->withQueryString();

        // Summary totals over the full filtered set, not just this page.
        $summaryQuery = Sale::query();
        $this->applySaleFilters($summaryQuery, $request);
        $totalTransactions = (clone $summaryQuery)->count();
        $totalAmount = (clone $summaryQuery)->sum('total_amount');

        $itemsQuery = DB::table('sale_items')->join('sales', 'sales.id', '=', 'sale_items.sale_id');
        if ($request->filled('date_from')) {
            $itemsQuery->whereDate('sales.created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $itemsQuery->whereDate('sales.created_at', '<=', $request->date_to);
        }
        if ($request->filled('receipt')) {
            $itemsQuery->where('sales.sale_number', 'like', '%' . $request->receipt . '%');
        }
        if ($request->filled('product_id')) {
            $itemsQuery->where('sale_items.product_id', $request->product_id);
        }
        $totalItemsSold = (int) $itemsQuery->sum('sale_items.quantity');

        $products = Product::orderBy('name')->get();

        return view('sales.log', compact('sales', 'totalTransactions', 'totalAmount', 'totalItemsSold', 'products'));
    }

    /**
     * Downloadable PDF of the Sales Records — respects whatever filters
     * are currently applied on the page (date range, product, receipt #).
     * Contains, per transaction: receipt number, date/time, cashier,
     * payment method, and every line item (product, quantity, unit price,
     * subtotal), plus a per-sale total and an overall grand total.
     */
    public function exportPdf(Request $request)
    {
        $query = Sale::with(['items.product'])->withCount('items');
        $this->applySaleFilters($query, $request);
        $sales = $query->latest()->get();

        $grandTotal = $sales->sum('total_amount');

        $filters = [
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'receipt' => $request->receipt,
        ];

        $pdf = Pdf::loadView('sales.pdf', compact('sales', 'grandTotal', 'filters'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('sales-report-' . now()->format('Y-m-d_His') . '.pdf');
    }

    private function applySaleFilters($query, Request $request)
    {
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('receipt')) {
            $query->where('sale_number', 'like', '%' . $request->receipt . '%');
        }
        if ($request->filled('product_id')) {
            $query->whereHas('items', function ($itemQuery) use ($request) {
                $itemQuery->where('product_id', $request->product_id);
            });
        }
        return $query;
    }
}