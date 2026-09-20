<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('condition_key')->unique();
            $table->enum('severity', ['low', 'medium', 'high', 'critical']);
            $table->boolean('active')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('signal_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('signal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipment_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamp('triggered_at')->useCurrent();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['signal_id', 'shipment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signal_events');
        Schema::dropIfExists('signals');
    }
};
