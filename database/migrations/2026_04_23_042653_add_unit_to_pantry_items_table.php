<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add a nullable 'unit' column to pantry_items.
     * Examples: "pcs", "kg", "g", "L", "ml", "pack", "box", "bottle"
     */
    public function up(): void
    {
        Schema::table('pantry_items', function (Blueprint $table) {
            $table->string('unit', 20)->nullable()->default('pcs')->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('pantry_items', function (Blueprint $table) {
            $table->dropColumn('unit');
        });
    }
};
