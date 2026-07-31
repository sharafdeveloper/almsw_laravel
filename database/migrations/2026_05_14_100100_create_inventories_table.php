<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 14, 2)->default(0);
            $table->decimal('price', 14, 2)->default(0);
            $table->decimal('weight', 14, 2)->default(0);
            $table->decimal('avg_price', 14, 2)->default(0);
            $table->timestamps();
            $table->unique('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
