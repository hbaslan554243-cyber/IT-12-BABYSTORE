<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Products table.
     * Directly solves Problem 1 (Inaccurate Inventory Monitoring):
     * - quantity_on_hand replaces "sight and memory" stock checks.
     * - reorder_point replaces the informal, reactive restocking trigger.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique()->comment('Simple product code, e.g. DIAP-M-01');
            $table->string('name');
            $table->string('category')->nullable()->comment('e.g. Diapers, Milk, Lotion');
            $table->string('unit')->default('pc')->comment('pc, pack, box, etc.');
            $table->decimal('selling_price', 10, 2);
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->integer('quantity_on_hand')->default(0);
            $table->integer('reorder_point')->default(5)->comment('Low-stock threshold that triggers a restock alert');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
