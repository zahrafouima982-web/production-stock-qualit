<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('components', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('unit_of_measure'); // e.g. pcs, meters, rolls

            // Cached balance, kept in sync with stock_movements inside DB transactions.
            // stock_movements remains the source of truth / audit ledger.
            $table->decimal('current_quantity', 12, 2)->default(0);
            $table->decimal('safety_stock_threshold', 12, 2)->default(0);

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('components');
    }
};
