<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pantry_items', function (Blueprint $table) {
        $table->id();
        $table->string('item_name');      // Name of the food
        $table->integer('quantity')->default(1);
        $table->date('expiry_date');      // Important for your alerts!
        $table->string('ripeness_info')->nullable(); // For Gemini AI results
        $table->string('category')->nullable();      // e.g., Veggie, Fruit, Dairy
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pantry_items');
    }
};
