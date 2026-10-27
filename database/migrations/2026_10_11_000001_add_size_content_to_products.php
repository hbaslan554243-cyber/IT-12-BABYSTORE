<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('size_content')->nullable()->after('size');
            $table->decimal('size_content_amount', 12, 3)->nullable()->after('size_content');
        });

        // Preserve existing size codes as-is, including legacy values outside the new dropdown.
        DB::table('products')->whereNotNull('size')->where('size', '<>', '')->update([
            'size_content' => DB::raw('size'),
        ]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['size_content', 'size_content_amount']);
        });
    }
};
