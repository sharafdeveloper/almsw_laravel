<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashbook_entries', function (Blueprint $table) {
            $table->id();
            $table->date('entry_date');
            $table->enum('type', ['in', 'out']);
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('description')->nullable();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->timestamps();
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashbook_entries');
    }
};
