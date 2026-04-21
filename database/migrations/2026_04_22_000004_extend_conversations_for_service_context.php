<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->foreignId('service_request_id')->nullable()->after('job_id')->constrained('service_requests')->nullOnDelete();
            $table->foreignId('service_proposal_id')->nullable()->after('service_request_id')->constrained('service_proposals')->nullOnDelete();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE conversations MODIFY type ENUM('direct', 'application', 'service') NOT NULL DEFAULT 'direct'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE conversations MODIFY type ENUM('direct', 'application') NOT NULL DEFAULT 'direct'");
        }

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_request_id');
            $table->dropConstrainedForeignId('service_proposal_id');
        });
    }
};
