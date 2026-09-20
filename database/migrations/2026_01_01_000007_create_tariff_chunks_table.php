<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tariff_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('tariff_documents')->cascadeOnDelete();
            $table->unsignedInteger('chunk_index');
            $table->text('chunk_text');
            $table->timestamp('created_at')->useCurrent();
        });

        // pgvector has no Laravel schema-builder support, so the column and its
        // similarity index are added with raw SQL. Dimension matches nomic-embed-text (768).
        DB::statement('ALTER TABLE tariff_chunks ADD COLUMN embedding vector(768)');
        DB::statement('CREATE INDEX tariff_chunks_embedding_hnsw ON tariff_chunks USING hnsw (embedding vector_cosine_ops)');
    }

    public function down(): void
    {
        Schema::dropIfExists('tariff_chunks');
    }
};
