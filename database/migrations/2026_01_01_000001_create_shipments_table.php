<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->enum('direction', ['import', 'export']);
            $table->string('origin_country');
            $table->string('destination_country');
            $table->text('product_description');
            $table->string('hs_code')->nullable();
            $table->decimal('declared_value', 14, 2);
            $table->string('declared_currency', 3)->default('USD');
            $table->enum('mode', ['air', 'sea', 'road', 'rail']);
            $table->string('incoterms', 10)->nullable();
            $table->date('expected_date')->nullable();
            $table->dateTime('filing_deadline')->nullable();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['draft', 'documents_pending', 'ready', 'filed', 'cleared', 'held'])
                ->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['origin_country', 'destination_country', 'mode']);
            $table->index('filing_deadline');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
