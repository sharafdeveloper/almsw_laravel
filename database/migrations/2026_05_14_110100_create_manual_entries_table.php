<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_entries', function (Blueprint $table) {
            $table->id();
            $table->enum('entry_type', ['other_asset', 'other_liability', 'drawing', 'other_expense', 'other_revenue']);
            $table->string('name');
            $table->decimal('amount', 14, 2)->default(0);
            $table->date('entry_date');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['entry_type', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_entries');
    }
};
