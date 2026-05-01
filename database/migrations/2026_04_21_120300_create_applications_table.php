<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('cv_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->json('cover_letter')->nullable();
            $table->enum('status', [
                'submitted',
                'under_review',
                'shortlisted',
                'interview_scheduled',
                'offered',
                'accepted',
                'rejected',
                'withdrawn',
            ])->default('submitted');
            $table->unsignedTinyInteger('match_percentage')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['job_id', 'user_id']);
            $table->index(['job_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
