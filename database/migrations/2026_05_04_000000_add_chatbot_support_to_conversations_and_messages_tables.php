<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('conversations', function (Blueprint $table): void {
                $table->string('type', 30)->default('direct')->change();
            });
        } elseif (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE conversations MODIFY type ENUM('direct', 'application', 'service', 'chatbot') NOT NULL DEFAULT 'direct'");
        }

        Schema::table('messages', function (Blueprint $table): void {
            $table->enum('message_role', ['user', 'assistant', 'system'])->default('user')->after('sender_id');
            $table->foreignId('sender_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table): void {
            $table->dropColumn('message_role');
            $table->foreignId('sender_id')->nullable(false)->change();
        });

        if (DB::getDriverName() === 'sqlite') {
            Schema::table('conversations', function (Blueprint $table): void {
                $table->string('type', 30)->default('direct')->change();
            });
        } elseif (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE conversations MODIFY type ENUM('direct', 'application', 'service') NOT NULL DEFAULT 'direct'");
        }
    }
};
