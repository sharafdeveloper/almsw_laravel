<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers');
            $table->date('bill_date');
            $table->text('description')->nullable();
            $table->decimal('sub_total', 14, 2)->default(0);
            $table->decimal('labour_cost', 14, 2)->default(0);
            $table->decimal('loading', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->decimal('cash_received', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_invoices');
    }
};
