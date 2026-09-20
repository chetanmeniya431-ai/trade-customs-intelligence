<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tariff_documents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('country_code', 10)->nullable();
            $table->enum('document_type', ['tariff_schedule', 'regulation', 'trade_agreement']);
            $table->string('file_path');
            $table->unsignedInteger('chunk_count')->default(0);
            $table->timestamp('embedded_at')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tariff_documents');
    }
};
