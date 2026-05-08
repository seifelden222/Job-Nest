<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedBigInteger('ai_user_id')->nullable()->unique()->after('status');
        });

        Schema::table('jobs', function (Blueprint $table): void {
            $table->unsignedBigInteger('ai_job_id')->nullable()->unique()->after('applications_count');
        });

        Schema::table('courses', function (Blueprint $table): void {
            $table->unsignedBigInteger('ai_course_id')->nullable()->unique()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->dropUnique(['ai_course_id']);
            $table->dropColumn('ai_course_id');
        });

        Schema::table('jobs', function (Blueprint $table): void {
            $table->dropUnique(['ai_job_id']);
            $table->dropColumn('ai_job_id');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['ai_user_id']);
            $table->dropColumn('ai_user_id');
        });
    }
};
