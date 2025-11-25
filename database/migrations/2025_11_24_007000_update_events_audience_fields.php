<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Allow LSG events without a society and add human-readable audience notes.
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['society_id']);
        });

        DB::statement('ALTER TABLE events MODIFY society_id BIGINT UNSIGNED NULL');

        Schema::table('events', function (Blueprint $table) {
            $table->foreign('society_id')->references('id')->on('societies')->nullOnDelete();
            $table->text('audience_notes')->nullable()->after('audience');
        });

        DB::table('events')
            ->orderBy('id')
            ->chunkById(100, function ($events) {
                foreach ($events as $event) {
                    $normalizedAudience = match ($event->audience) {
                        'society' => 'society_members',
                        'all' => ($event->is_ceit_wide || in_array($event->type, ['ceit', 'lsg'], true)) ? 'ceit_students' : 'society_members',
                        'officers_only' => 'all_officers',
                        'lsg_and_society_officers' => 'all_officers',
                        'society_officers' => 'society_officers',
                        'lsg_officers' => 'lsg_officers',
                        'year_specific' => 'others',
                        default => $event->audience,
                    };

                    $notes = null;
                    if ($event->audience === 'year_specific' && $event->audience_years) {
                        $notes = 'Year levels: '.$event->audience_years;
                    } elseif ($event->audience === 'others' && $event->audience_notes) {
                        $notes = $event->audience_notes;
                    }

                    DB::table('events')
                        ->where('id', $event->id)
                        ->update([
                            'audience' => $normalizedAudience,
                            'audience_notes' => $notes,
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['society_id']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('audience_notes');
        });

        DB::statement('ALTER TABLE events MODIFY society_id BIGINT UNSIGNED NOT NULL');

        Schema::table('events', function (Blueprint $table) {
            $table->foreign('society_id')->references('id')->on('societies')->cascadeOnDelete();
        });
    }
};
