<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    /**
     * Point-of-Sale module — solves Problem 2 (Inefficient and Error-Prone
     * Sales Transactions). Totals are computed by the system instead of
     * mental arithmetic, every item/quantity is recorded (unlike the old
     * logbook that only kept a daily total), and a receipt is generated
     * for every transaction.
     */
    public function create()
    {
        // Only show products that are actually in stock, so staff can't
        // accidentally sell something that isn't there. With large
        // catalogs, the view lets staff search by name/SKU and narrow by
        // category instead of scrolling a long list.
        $products = Product::where('quantity_on_hand', '>', 0)->orderBy('name')->get();
        $categories = Product::CATEGORIES;

        // Built here (not inline in the Blade view) because Blade's @json
        // directive splits its argument on every top-level comma — which
        // breaks on a multi-field array literal like this one. Passing a
        // single already-built variable to @json() sidesteps that entirely.
        $productsForJs = $products->map(fn ($p) => [
            'id' => $p->id,
            'label' => $p->name . ' — ' . $p->sku,
            'name' => $p->name,
            'sku' => $p->sku,
            'category' => $p->category,
            'price' => (float) $p->selling_price,
            'stock' => $p->quantity_on_hand,
        ])->values();

        return view('sales.create', compact('products', 'categories', 'productsForJs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cashier_name' => 'nullable|string|max:100',
            'payment_method' => 'required|in:cash,gcash',
            'cash_received' => 'nullable|numeric|min:0',
            'gcash_reference' => 'nullable|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ], [
            'items.required' => 'Add at least one item before completing the sale.',
        ]);

        if ($validated['payment_method'] === 'cash' && !$request->filled('cash_received')) {
            throw ValidationException::withMessages(['cash_received' => 'Please enter the cash amount received.']);
        }

        if ($validated['payment_method'] === 'gcash' && !$request->filled('gcash_reference')) {
            throw ValidationException::withMessages(['gcash_reference' => 'Please enter the GCash reference number.']);
        }

        $sale = DB::transaction(function () use ($validated) {
            $total = 0;
            $lineItems = [];

            // Lock each product row while we read/deduct stock, so two
            // simultaneous sales at the counter can never oversell an item.
            foreach ($validated['items'] as $item) {
                $product = Product::where('id', $item['product_id'])->lockForUpdate()->first();

                if (!$product) {
                    throw ValidationException::withMessages(['items' => 'A selected product no longer exists.']);
                }

                if ($product->quantity_on_hand < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Not enough stock for {$product->name}. Only {$product->quantity_on_hand} left.",
                    ]);
                }

                $subtotal = $product->selling_price * $item['quantity'];
                $total += $subtotal;

                $lineItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->selling_price,
                    'subtotal' => $subtotal,
                ];
            }

            if ($validated['payment_method'] === 'cash') {
                $cashReceived = (float) $validated['cash_received'];

                // Core error-handling requirement: cash received must never
                // be less than the total amount due.
                if ($cashReceived < $total) {
                    throw ValidationException::withMessages([
                        'cash_received' => 'Cash received (₱' . number_format($cashReceived, 2) . ') is lower than the total amount due (₱' . number_format($total, 2) . ').',
                    ]);
                }
                $changeDue = $cashReceived - $total;
            } else {
                // GCash payments are settled exactly through the app;
                // there is no physical change to hand back.
                $cashReceived = $total;
                $changeDue = 0;
            }

            $sale = Sale::create([
                'sale_number' => 'PENDING-' . uniqid(),
                'cashier_name' => $validated['cashier_name'] ?? (auth()->user()->name ?? 'Staff'),
                'user_id' => auth()->id(),
                'payment_method' => $validated['payment_method'],
                'gcash_reference' => $validated['gcash_reference'] ?? null,
                'total_amount' => $total,
                'cash_received' => $cashReceived,
                'change_due' => $changeDue,
            ]);

            foreach ($lineItems as $line) {
                $sale->items()->create([
                    'product_id' => $line['product']->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'unit_cost_at_sale' => $line['product']->cost_price,
                    'subtotal' => $line['subtotal'],
                ]);

                DB::table('inventory_movements')->insert([
                    'product_id' => $line['product']->id,
                    'type' => 'sale',
                    'quantity_delta' => -$line['quantity'],
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'occurred_at' => now(),
                    'note' => 'Stock sold',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // This single line is the direct fix for the paper's finding
                // that "no automatic deduction from stock after a sale" occurs.
                $line['product']->decrement('quantity_on_hand', $line['quantity']);
            }

            // Receipt number is derived from the row's own DB auto-increment
            // id — guaranteed unique and strictly increasing, unlike a
            // date+daily-count scheme which can collide under concurrent
            // sales and resets meaninglessly every day.
            $sale->update(['sale_number' => 'SO-' . str_pad($sale->id, 6, '0', STR_PAD_LEFT)]);

            return $sale;
        });

        return redirect()->route('sales.show', $sale)->with('status', 'Sale recorded successfully.');
    }

    public function show(Sale $sale)
    {
        $sale->load('items.product');

        return view('sales.show', compact('sale'));
    }
}
