<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /**
     * Purchase Order module — a supporting restocking feature, kept in the
     * system alongside the three paper problems (Inventory, Sales, and
     * Itemized Records) even though it isn't one of them. It formally
     * documents supplier orders instead of relying on verbal or Facebook
     * Messenger arrangements: who supplied it, what was ordered, at what
     * cost, expected vs. actual delivery date, and how much actually
     * arrived.
     */
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with('supplier')->latest('order_date')->paginate(15);

        return view('purchase_orders.index', compact('purchaseOrders'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        // Pre-select low-stock products to make reordering faster, tying
        // this module directly to the inventory alert from Problem 1.
        $products = Product::orderBy('name')->get();

        return view('purchase_orders.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        $purchaseOrder = DB::transaction(function () use ($validated) {
            $total = 0;
            foreach ($validated['items'] as $item) {
                $total += $item['quantity_ordered'] * $item['unit_cost'];
            }

            $po = PurchaseOrder::create([
                'po_number' => 'PENDING-' . uniqid(),
                'supplier_id' => $validated['supplier_id'],
                'user_id' => auth()->id(),
                'status' => 'pending',
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'total_amount' => $total,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $po->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity_ordered' => $item['quantity_ordered'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $item['quantity_ordered'] * $item['unit_cost'],
                ]);
            }

            $po->update(['po_number' => 'PO-' . str_pad($po->id, 6, '0', STR_PAD_LEFT)]);

            return $po;
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('status', 'Purchase order created and recorded.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('items.product', 'supplier');

        return view('purchase_orders.show', compact('purchaseOrder'));
    }

    /**
     * Marks a purchase order as delivered and updates stock accordingly.
     * This is what "closes the loop" between this supporting restocking
     * module and Problem 1 (accurate stock levels) — delivery quantities recorded
     * here flow directly into quantity_on_hand instead of being estimated
     * later during a manual recount.
     */
    public function markDelivered(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'delivered' => 'required|array',
            'delivered.*' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($purchaseOrder, $validated) {
            foreach ($purchaseOrder->items as $item) {
                $deliveredQty = $validated['delivered'][$item->id] ?? 0;

                if ($deliveredQty > 0) {
                    $item->update(['quantity_delivered' => $deliveredQty]);
                    $item->product->increment('quantity_on_hand', $deliveredQty);
                    DB::table('inventory_movements')->insert([
                        'product_id' => $item->product_id,
                        'type' => 'receipt',
                        'quantity_delta' => $deliveredQty,
                        'reference_type' => 'purchase_order',
                        'reference_id' => $purchaseOrder->id,
                        'occurred_at' => now(),
                        'note' => 'Purchase order delivery received',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $purchaseOrder->update([
                'status' => 'delivered',
                'delivered_date' => now()->toDateString(),
            ]);
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('status', 'Delivery recorded and stock updated.');
    }
}
