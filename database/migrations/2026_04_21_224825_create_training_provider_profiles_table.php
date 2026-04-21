<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_provider_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('provider_name');
            $table->string('website')->nullable();
            $table->string('industry')->nullable();
            $table->string('location')->nullable();
            $table->text('about')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->unsignedTinyInteger('onboarding_step')->default(1);
            $table->boolean('is_profile_completed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_provider_profiles');
    }
};
