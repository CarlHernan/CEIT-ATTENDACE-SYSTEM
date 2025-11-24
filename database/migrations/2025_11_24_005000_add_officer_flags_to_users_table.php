<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_society_officer')->default(false)->after('role_id');
            $table->boolean('is_lsg_officer')->default(false)->after('is_society_officer');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_society_officer', 'is_lsg_officer']);
        });
    }
};
