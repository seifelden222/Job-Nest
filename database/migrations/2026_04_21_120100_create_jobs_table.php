<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('users')->cascadeOnDelete();
            $table->json('title');
            $table->json('description');
            $table->string('location')->nullable();
            $table->string('employment_type')->nullable();
            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();
            $table->string('currency', 10)->nullable();
            $table->string('experience_level')->nullable();
            $table->json('requirements')->nullable();
            $table->json('responsibilities')->nullable();
            $table->date('deadline')->nullable();
            $table->enum('status', ['draft', 'active', 'closed', 'archived'])->default('draft');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('applications_count')->default(0);
            $table->timestamps();

            $table->index(['status', 'is_active']);
            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
