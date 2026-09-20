<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_embeddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->unique()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE shipment_embeddings ADD COLUMN embedding vector(768)');
        DB::statement('CREATE INDEX shipment_embeddings_embedding_hnsw ON shipment_embeddings USING hnsw (embedding vector_cosine_ops)');
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_embeddings');
    }
};
