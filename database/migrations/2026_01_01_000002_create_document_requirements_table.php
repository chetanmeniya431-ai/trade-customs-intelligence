<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_requirements', function (Blueprint $table) {
            $table->id();
            $table->string('origin_country');
            $table->string('destination_country');
            $table->enum('mode', ['air', 'sea', 'road', 'rail']);
            $table->string('document_type');
            $table->boolean('required')->default(true);
            $table->string('conditional_on')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['origin_country', 'destination_country', 'mode'], 'doc_req_route_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_requirements');
    }
};
