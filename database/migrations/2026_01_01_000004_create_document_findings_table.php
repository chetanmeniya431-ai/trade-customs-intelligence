<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_id')->nullable()->constrained('shipment_documents')->nullOnDelete();
            $table->enum('finding_type', ['hs_mismatch', 'value_mismatch', 'missing_doc', 'expired_doc', 'other']);
            $table->text('description');
            $table->string('suggested_value')->nullable();
            $table->enum('severity', ['low', 'medium', 'high', 'critical']);
            $table->enum('status', ['open', 'confirmed', 'resolved'])->default('open');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_note')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['shipment_id', 'status']);
            $table->index('finding_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_findings');
    }
};
