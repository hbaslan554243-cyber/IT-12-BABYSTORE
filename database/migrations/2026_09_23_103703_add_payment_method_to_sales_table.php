<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds payment method support to the POS module: Cash or GCash.
     * Kept as a separate migration (rather than editing the original
     * sales migration) so it applies cleanly even if the base tables
     * were already migrated from an earlier version of this project.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'gcash'])->default('cash')->after('cashier_name');
            $table->string('gcash_reference')->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'gcash_reference']);
        });
    }
};
