<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Purchase Orders + Purchase Order Items tables.
     * Supports Problem 1 (Inventory) by formally recording supplier restocking / deliveries:
     * - Replaces verbal / Facebook-Messenger-only reorder arrangements with a
     *   formal, stored purchase order (supplier, items, quantity, cost, dates).
     * - status tracks pending -> delivered, so orders can be verified against
     *   what was actually received.
     * - Marking a PO "delivered" automatically increases product stock,
     *   closing the loop with the Inventory module (Problem 1).
     */
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('supplier_id')->constrained();
            $table->enum('status', ['pending', 'delivered', 'cancelled'])->default('pending');
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->date('delivered_date')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->integer('quantity_ordered');
            $table->integer('quantity_delivered')->default(0);
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
    }
};
