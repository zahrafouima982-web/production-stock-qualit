<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_defects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quality_inspection_id')->constrained('quality_inspections')->restrictOnDelete();
            $table->enum('defect_type', ['DIMENSIONAL', 'ELECTRICAL', 'COSMETIC', 'ASSEMBLY', 'OTHER']);
            $table->text('description');
            $table->enum('severity', ['MINOR', 'MAJOR', 'CRITICAL']);
            $table->foreignId('detected_by')->constrained('users')->restrictOnDelete();
            $table->enum('status', ['OPEN', 'IN_PROGRESS', 'RESOLVED', 'CLOSED'])->default('OPEN');
            $table->timestamps();

            $table->index('quality_inspection_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_defects');
    }
};
