<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('jobs') && ! Schema::hasTable('queue_jobs')) {
            Schema::rename('jobs', 'queue_jobs');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('queue_jobs') && ! Schema::hasTable('jobs')) {
            Schema::rename('queue_jobs', 'jobs');
        }
    }
};
