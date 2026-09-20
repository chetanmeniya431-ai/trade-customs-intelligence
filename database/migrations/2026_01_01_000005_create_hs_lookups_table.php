<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hs_lookups', function (Blueprint $table) {
            $table->id();
            $table->text('query_text');
            $table->json('suggested_codes')->nullable();
            $table->enum('confidence', ['high', 'medium', 'low'])->nullable();
            $table->string('source_section')->nullable();
            $table->text('result_summary')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hs_lookups');
    }
};
