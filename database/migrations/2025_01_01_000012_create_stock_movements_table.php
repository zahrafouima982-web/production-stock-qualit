<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_id')->constrained('components')->restrictOnDelete();
            $table->enum('type', ['IN', 'OUT']);
            $table->decimal('quantity', 12, 2); // always positive; sign implied by type
            $table->string('reference')->nullable(); // e.g. supplier doc / linked PO number
            $table->foreignId('performed_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('performed_at');
            $table->timestamps();

            $table->index(['component_id', 'performed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
