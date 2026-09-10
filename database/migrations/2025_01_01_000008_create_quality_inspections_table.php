<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_inspections', function (Blueprint $table) {
            $table->id();
            // Linked to a production_record rather than an order, so reinspections
            // (a new row here) stay tied to the exact batch that was produced.
            $table->foreignId('production_record_id')->constrained('production_records')->restrictOnDelete();
            $table->foreignId('inspector_id')->constrained('users')->restrictOnDelete();
            $table->enum('result', ['PENDING', 'PASS', 'FAIL'])->default('PENDING');
            $table->timestamp('inspected_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('production_record_id');
            $table->index('result');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_inspections');
    }
};
