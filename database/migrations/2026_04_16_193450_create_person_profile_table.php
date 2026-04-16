<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('person_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('university')->nullable();
            $table->string('major')->nullable();
            $table->string('current_job_title')->nullable();
            $table->string('employment_status')->nullable();
            $table->decimal('expected_salary_min', 10, 2)->nullable();
            $table->decimal('expected_salary_max', 10, 2)->nullable();
            $table->enum('preferred_work_location', ['onsite', 'remote', 'hybrid'])->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->text('about')->nullable();
            $table->unsignedTinyInteger('onboarding_step')->default(1);
            $table->boolean('is_profile_completed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('person_profiles');
    }
};
