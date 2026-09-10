<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('component_id')->constrained('components')->restrictOnDelete();
            $table->enum('status', ['ACTIVE', 'RESOLVED'])->default('ACTIVE');
            $table->timestamp('triggered_at');
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // NOTE: "only one ACTIVE alert per component" is NOT enforced here as a DB
            // constraint (MySQL has no native partial/conditional unique index).
            // It must be enforced in the Stock service: check-then-create inside the
            // same locked transaction as the triggering stock movement.
            $table->index(['component_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_alerts');
    }
};
