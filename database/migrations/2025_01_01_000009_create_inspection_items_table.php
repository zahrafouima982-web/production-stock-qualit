<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quality_inspection_id')->constrained('quality_inspections')->cascadeOnDelete();
            $table->string('criterion_name'); // e.g. "Dimensional Check", "Continuity Test"
            $table->enum('result', ['PASS', 'FAIL', 'N_A'])->default('N_A');
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->index('quality_inspection_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_items');
    }
};
