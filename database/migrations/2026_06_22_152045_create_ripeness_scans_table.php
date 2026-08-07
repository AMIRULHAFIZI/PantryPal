<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ripeness_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('image_path')->nullable();
            $table->string('item_name')->nullable();
            $table->string('ripeness_level')->nullable();
            $table->integer('ripeness_score')->nullable();
            $table->text('color_description')->nullable();
            $table->integer('shelf_life_days')->nullable();
            $table->text('recommendation')->nullable();
            $table->text('storage_tip')->nullable();
            $table->boolean('is_success')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ripeness_scans');
    }
};
