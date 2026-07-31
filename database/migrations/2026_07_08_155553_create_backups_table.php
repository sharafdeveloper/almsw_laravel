<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {

            $table->id();

            $table->string('file_name');

            $table->string('original_name');

            $table->enum('type', [
                'database',
                'application'
            ]);

            $table->string('file_path');

            $table->unsignedBigInteger('file_size');

            $table->string('mime_type')->nullable();

            $table->string('created_by')->nullable();

            $table->timestamps();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};