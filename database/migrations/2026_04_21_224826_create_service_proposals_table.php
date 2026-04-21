<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained('service_requests')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->decimal('proposed_budget', 10, 2)->nullable();
            $table->unsignedInteger('delivery_days')->nullable();
            $table->enum('status', ['submitted', 'accepted', 'rejected', 'withdrawn'])->default('submitted');
            $table->timestamps();

            $table->unique(['service_request_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_proposals');
    }
};
