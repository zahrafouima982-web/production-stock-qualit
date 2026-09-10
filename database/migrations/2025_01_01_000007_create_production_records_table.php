<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained('production_orders')->restrictOnDelete();
            $table->unsignedInteger('produced_quantity');
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('recorded_at');
            $table->string('shift')->nullable();
            $table->timestamps();

            $table->index('production_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_records');
    }
};
