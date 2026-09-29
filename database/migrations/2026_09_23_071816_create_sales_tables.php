<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sales + Sale Items tables.
     * Directly solves Problem 2 (Inefficient and Error-Prone Sales Transactions):
     * - Totals are computed by the system, not by memory/mental arithmetic.
     * - Every sale is itemized (sale_items), unlike the old logbook that
     *   recorded only a single daily total with no items or quantities.
     * - Every sale generates a retrievable digital receipt.
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('sale_number')->unique();
            $table->string('cashier_name')->nullable()->comment('Owner or staff who processed the sale');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('cash_received', 10, 2)->default(0);
            $table->decimal('change_due', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
