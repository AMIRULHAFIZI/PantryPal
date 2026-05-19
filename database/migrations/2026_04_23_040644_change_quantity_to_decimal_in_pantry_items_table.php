<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Change quantity from integer to decimal(8,3) to support weight-based items
     * e.g. "CHIC THIGH KG" = 0.848 kg from a receipt
     */
    public function up(): void
    {
        Schema::table('pantry_items', function (Blueprint $table) {
            $table->decimal('quantity', 8, 3)->default(1)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pantry_items', function (Blueprint $table) {
            $table->integer('quantity')->default(1)->change();
        });
    }
};
