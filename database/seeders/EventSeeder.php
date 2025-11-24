<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Society;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $officer = User::whereHas('role', fn ($q) => $q->where('slug', 'officer'))->first();
        $psits = Society::where('slug', 'psits')->first();
        $pice = Society::where('slug', 'pice')->first();
        $icpep = Society::where('slug', 'icpep')->first();

        if ($psits && $officer) {
            Event::firstOrCreate(
                ['title' => 'PSITS General Assembly'],
                [
                    'description' => 'Society GA for PSITS members.',
                    'start_at' => $now->copy()->addDays(3),
                    'end_at' => $now->copy()->addDays(3)->addHours(2),
                    'location' => 'Auditorium',
                    'attendance_mode' => 'qr',
                    'is_ceit_wide' => false,
                    'type' => 'society',
                    'template' => 'GA',
                    'audience' => 'society',
                    'audience_years' => null,
                    'require_timeout' => true,
                    'status' => 'active',
                    'visibility' => 'students',
                    'society_id' => $psits->id,
                    'created_by' => $officer->id,
                ]
            );
        }

        if ($pice && $officer) {
            Event::firstOrCreate(
                ['title' => 'PICE Seminar on Structural Safety'],
                [
                    'description' => 'Seminar for Civil Engineering students.',
                    'start_at' => $now->copy()->addDays(5),
                    'end_at' => $now->copy()->addDays(5)->addHours(3),
                    'location' => 'CEIT Hall',
                    'attendance_mode' => 'hybrid',
                    'is_ceit_wide' => false,
                    'type' => 'society',
                    'template' => 'Seminar',
                    'audience' => 'year_specific',
                    'audience_years' => '3,4',
                    'require_timeout' => false,
                    'status' => 'active',
                    'visibility' => 'students',
                    'society_id' => $pice->id,
                    'created_by' => $officer->id,
                ]
            );
        }

        if ($icpep && $officer) {
            Event::firstOrCreate(
                ['title' => 'ICPEP Officers Meeting'],
                [
                    'description' => 'Committee planning session.',
                    'start_at' => $now->copy()->addDays(2),
                    'end_at' => $now->copy()->addDays(2)->addHours(1),
                    'location' => 'Lab 3',
                    'attendance_mode' => 'manual',
                    'is_ceit_wide' => false,
                    'type' => 'meeting',
                    'template' => 'Meeting',
                    'audience' => 'officers_only',
                    'audience_years' => null,
                    'require_timeout' => false,
                    'status' => 'active',
                    'visibility' => 'officers',
                    'society_id' => $icpep->id,
                    'created_by' => $officer->id,
                ]
            );
        }

        // CEIT-wide sample
        if ($psits && $officer) {
            Event::firstOrCreate(
                ['title' => 'CEIT Research Forum'],
                [
                    'description' => 'Department-wide forum for CEIT students.',
                    'start_at' => $now->copy()->addDays(7),
                    'end_at' => $now->copy()->addDays(7)->addHours(4),
                    'location' => 'Main Hall',
                    'attendance_mode' => 'qr',
                    'is_ceit_wide' => true,
                    'type' => 'ceit',
                    'template' => 'Seminar',
                    'audience' => 'all',
                    'audience_years' => null,
                    'require_timeout' => true,
                    'status' => 'active',
                    'visibility' => 'students',
                    'society_id' => $psits->id,
                    'created_by' => $officer->id,
                ]
            );
        }
    }
}
