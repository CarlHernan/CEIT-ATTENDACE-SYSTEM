<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('type')->default('society'); // society, ceit, lsg, meeting
            $table->string('template')->nullable(); // GA, Meeting, Seminar, etc
            $table->string('audience')->default('society'); // society, year_specific, all, officers_only
            $table->string('audience_years')->nullable(); // comma-separated years when audience = year_specific
            $table->boolean('require_timeout')->default(true);
            $table->string('status')->default('active'); // active, cancelled
            $table->string('visibility')->default('students'); // students, officers, all
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'template',
                'audience',
                'audience_years',
                'require_timeout',
                'status',
                'visibility',
            ]);
        });
    }
};
