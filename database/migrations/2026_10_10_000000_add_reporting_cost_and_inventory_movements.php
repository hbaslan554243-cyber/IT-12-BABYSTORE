<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('unit_cost_at_sale', 10, 2)->nullable()->after('unit_price');
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // receipt, sale, adjustment
            $table->integer('quantity_delta');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->dateTime('occurred_at');
            $table->string('note')->nullable();
            $table->timestamps();
            $table->index(['type', 'occurred_at']);
            $table->index(['reference_type', 'reference_id']);
        });

        // Seed the movement history that can be reconstructed from existing transactions.
        DB::table('sale_items')->orderBy('id')->chunk(500, function ($items) {
            foreach ($items as $item) {
                DB::table('inventory_movements')->insert([
                    'product_id' => $item->product_id,
                    'type' => 'sale',
                    'quantity_delta' => -abs((int) $item->quantity),
                    'reference_type' => 'sale',
                    'reference_id' => $item->sale_id,
                    'occurred_at' => $item->created_at,
                    'note' => 'Imported from recorded sale',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        DB::table('purchase_order_items')
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->where('purchase_orders.status', 'delivered')
            ->where('purchase_order_items.quantity_delivered', '>', 0)
            ->select('purchase_order_items.product_id', 'purchase_order_items.purchase_order_id', 'purchase_order_items.quantity_delivered', 'purchase_orders.delivered_date')
            ->orderBy('purchase_order_items.id')
            ->chunk(500, function ($items) {
                foreach ($items as $item) {
                    DB::table('inventory_movements')->insert([
                        'product_id' => $item->product_id,
                        'type' => 'receipt',
                        'quantity_delta' => (int) $item->quantity_delivered,
                        'reference_type' => 'purchase_order',
                        'reference_id' => $item->purchase_order_id,
                        'occurred_at' => $item->delivered_date . ' 12:00:00',
                        'note' => 'Imported from recorded delivery',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('unit_cost_at_sale');
        });
    }
};
