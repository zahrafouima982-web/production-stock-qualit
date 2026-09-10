<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('corrective_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quality_defect_id')->constrained('quality_defects')->restrictOnDelete();
            $table->text('root_cause')->nullable();
            $table->text('action_description');
            $table->foreignId('responsible_user_id')->constrained('users')->restrictOnDelete();
            $table->enum('status', ['OPEN', 'IN_PROGRESS', 'DONE', 'VALIDATED'])->default('OPEN');
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->boolean('requires_reinspection')->default(false);
            $table->timestamps();

            $table->index('quality_defect_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('corrective_actions');
    }
};
