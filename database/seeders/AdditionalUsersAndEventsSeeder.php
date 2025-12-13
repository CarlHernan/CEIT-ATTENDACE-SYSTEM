<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Role;
use App\Models\Society;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdditionalUsersAndEventsSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::whereIn('slug', ['student', 'officer', 'lsg_officer'])->get()->keyBy('slug');
        $societies = Society::whereIn('slug', ['psits', 'pice', 'icpep', 'jiecep', 'psabe'])->get()->keyBy('slug');

        $psits = $societies['psits'] ?? null;

        $students = [
            [
                'name' => 'Christian James J. Perez',
                'email' => 'imaginarycjay@gmail.com',
                'id_number' => '23-12072',
                'course' => 'BSInfoSys',
                'section' => 'A',
                'year_level' => '3',
                'qr' => 'CHRISTIAN JAMES J. PEREZ,BSInfoSys,,',
                'role' => 'officer',
                'is_society_officer' => true,
                'is_lsg_officer' => false,
            ],
            [
                'name' => 'Ivan Raphael Abang',
                'email' => 's.irnabang@usm.edu.ph',
                'id_number' => '23-45747',
                'course' => 'BSInfoSys',
                'section' => 'A',
                'year_level' => '3',
                'qr' => 'IVAN RAPHAEL ABANG,BSInfoSys,,',
                'role' => 'student',
            ],
            [
                'name' => 'Angelo M. Duran',
                'email' => 's.amduran@usm.edu.ph',
                'id_number' => '23-40975',
                'course' => 'BSInfoSys',
                'section' => 'A',
                'year_level' => '4',
                'qr' => 'ANGELO M. DURAN,BSInfoSys,,',
                'role' => 'lsg_officer',
                'is_lsg_officer' => true,
            ],
            [
                'name' => 'Donald M. Miranda',
                'email' => 's.dmmiranda@usm.edu.ph',
                'id_number' => '23-39378',
                'course' => 'BSInfoSys',
                'section' => 'B',
                'year_level' => '2',
                'qr' => 'DONALD M. MIRANDA,BSInfoSys,,',
                'role' => 'student',
            ],
            [
                'name' => 'Adrian P. Dañucop',
                'email' => 's.apdanucop@usm.edu.ph',
                'id_number' => '23-86122',
                'course' => 'BSInfoSys',
                'section' => 'B',
                'year_level' => '3',
                'qr' => 'ADRIAN P. DAÑUCOP,BSInfoSys,,',
                'role' => 'student',
            ],
            [
                'name' => 'Kenth Ian Estrella Carado',
                'email' => 's.kiecarado@usm.edu.ph',
                'id_number' => '23-40173',
                'course' => 'BSInfoSys',
                'section' => 'C',
                'year_level' => '3',
                'qr' => 'KENTH IAN ESTRELLA CARADO,BSInfoSys,,',
                'role' => 'student',
            ],
            [
                'name' => 'John Allan Mark Gotera',
                'email' => 'jamgotera@gmail.com',
                'id_number' => '23-62942',
                'course' => 'BSInfoSys',
                'section' => 'C',
                'year_level' => '4',
                'qr' => 'JOHN ALLAN MARK GOTERA,BSInfoSys,,',
                'role' => 'student',
            ],
        ];

        foreach ($students as $data) {
            if (! $psits) {
                continue;
            }

            $roleSlug = $data['role'] ?? 'student';
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'id_number' => $data['id_number'],
                    'course' => $data['course'],
                    'section' => $data['section'] ?? null,
                    'year_level' => $data['year_level'] ?? null,
                    'department' => 'CEIT',
                    'qr_raw_text' => $data['qr'],
                    'role_id' => $roles[$roleSlug]?->id,
                    'is_society_officer' => $data['is_society_officer'] ?? false,
                    'is_lsg_officer' => $data['is_lsg_officer'] ?? false,
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]
            );

            $user->societies()->syncWithoutDetaching([
                $psits->id => ['position' => ($data['is_society_officer'] ?? false) ? 'Officer' : 'Member'],
            ]);
        }

        // Dummy students per society (one society officer, one LSG officer, one member)
        $dummyStudents = [
            [
                'society' => 'psits',
                'course' => 'BSIT',
                'students' => [
                    ['name' => 'Alexis T. Navarro', 'id' => '23-73001', 'email' => 'alexis.nav@psits.test', 'role' => 'officer', 'is_society_officer' => true],
                    ['name' => 'Loren S. Malvar', 'id' => '23-73002', 'email' => 'loren.malvar@psits.test', 'role' => 'lsg_officer', 'is_lsg_officer' => true],
                    ['name' => 'Mika D. Fabro', 'id' => '23-73003', 'email' => 'mika.fabro@psits.test', 'role' => 'student'],
                ],
            ],
            [
                'society' => 'pice',
                'course' => 'BSCE',
                'students' => [
                    ['name' => 'Rowan P. Castañeda', 'id' => '23-73010', 'email' => 'rowan.castaneda@pice.test', 'role' => 'officer', 'is_society_officer' => true],
                    ['name' => 'Elise V. Dizon', 'id' => '23-73011', 'email' => 'elise.dizon@pice.test', 'role' => 'lsg_officer', 'is_lsg_officer' => true],
                    ['name' => 'Joaquin R. Santos', 'id' => '23-73012', 'email' => 'joaquin.santos@pice.test', 'role' => 'student'],
                ],
            ],
            [
                'society' => 'icpep',
                'course' => 'BSCpE',
                'students' => [
                    ['name' => 'Tricia M. Yu', 'id' => '23-73020', 'email' => 'tricia.yu@icpep.test', 'role' => 'officer', 'is_society_officer' => true],
                    ['name' => 'Harvey Q. Lim', 'id' => '23-73021', 'email' => 'harvey.lim@icpep.test', 'role' => 'lsg_officer', 'is_lsg_officer' => true],
                    ['name' => 'Paolo J. Reyes', 'id' => '23-73022', 'email' => 'paolo.reyes@icpep.test', 'role' => 'student'],
                ],
            ],
            [
                'society' => 'jiecep',
                'course' => 'BSECE',
                'students' => [
                    ['name' => 'Tessa I. Robles', 'id' => '23-73030', 'email' => 'tessa.robles@jiecep.test', 'role' => 'officer', 'is_society_officer' => true],
                    ['name' => 'Miguel C. Yanga', 'id' => '23-73031', 'email' => 'miguel.yanga@jiecep.test', 'role' => 'lsg_officer', 'is_lsg_officer' => true],
                    ['name' => 'Darren O. Velasco', 'id' => '23-73032', 'email' => 'darren.velasco@jiecep.test', 'role' => 'student'],
                ],
            ],
            [
                'society' => 'psabe',
                'course' => 'BSABE',
                'students' => [
                    ['name' => 'Claire N. Mendoza', 'id' => '23-73040', 'email' => 'claire.mendoza@psabe.test', 'role' => 'officer', 'is_society_officer' => true],
                    ['name' => 'Ramon V. Bautista', 'id' => '23-73041', 'email' => 'ramon.bautista@psabe.test', 'role' => 'lsg_officer', 'is_lsg_officer' => true],
                    ['name' => 'Ina G. Lao', 'id' => '23-73042', 'email' => 'ina.lao@psabe.test', 'role' => 'student'],
                ],
            ],
        ];

        foreach ($dummyStudents as $group) {
            $society = $societies[$group['society']] ?? null;
            if (! $society) {
                continue;
            }
            $course = $group['course'];

            foreach ($group['students'] as $student) {
                $roleSlug = $student['role'] ?? 'student';
                $user = User::firstOrCreate(
                    ['email' => $student['email']],
                    [
                        'name' => $student['name'],
                        'id_number' => $student['id'],
                        'course' => $course,
                        'section' => 'A',
                        'year_level' => rand(1, 4),
                        'department' => 'CEIT',
                        'qr_raw_text' => strtoupper($student['name']).','.$course.',,',
                        'role_id' => $roles[$roleSlug]?->id,
                        'is_society_officer' => $student['is_society_officer'] ?? false,
                        'is_lsg_officer' => $student['is_lsg_officer'] ?? false,
                        'password' => Hash::make('password123'),
                        'email_verified_at' => now(),
                    ]
                );

                $user->societies()->syncWithoutDetaching([
                    $society->id => ['position' => ($student['is_society_officer'] ?? false) ? 'Officer' : 'Member'],
                ]);
            }
        }

        $now = Carbon::now();

        // CEIT/LSG events
        $ceitEvents = [
            [
                'title' => 'CEIT Innovation Summit',
                'audience' => 'ceit_students',
                'start_at' => $now->copy()->addDay()->setTime(8, 0),
                'end_at' => $now->copy()->addDay()->setTime(11, 0),
                'is_ceit_wide' => true,
                'type' => 'ceit',
                'template' => 'Summit',
                'attendance_mode' => 'hybrid',
            ],
            [
                'title' => 'LSG Officers Coordination Meeting',
                'audience' => 'lsg_officers',
                'start_at' => $now->copy()->addDays(3)->setTime(19, 0),
                'end_at' => $now->copy()->addDays(3)->setTime(21, 0),
                'is_ceit_wide' => true,
                'type' => 'lsg',
                'template' => 'Meeting',
                'attendance_mode' => 'manual',
            ],
            [
                'title' => 'Joint LSG + Society Officers Briefing',
                'audience' => 'all_officers',
                'start_at' => $now->copy()->subDays(1)->setTime(9, 0),
                'end_at' => $now->copy()->subDays(1)->setTime(11, 0),
                'is_ceit_wide' => true,
                'type' => 'lsg',
                'template' => 'Briefing',
                'attendance_mode' => 'hybrid',
            ],
        ];

        foreach ($ceitEvents as $eventData) {
            Event::firstOrCreate(
                ['title' => $eventData['title']],
                [
                    'description' => $eventData['template'].' for CEIT community',
                    'start_at' => $eventData['start_at'],
                    'end_at' => $eventData['end_at'],
                    'location' => 'Main Hall',
                    'attendance_mode' => $eventData['attendance_mode'],
                    'audience' => $eventData['audience'],
                    'audience_notes' => null,
                    'status' => 'active',
                    'society_id' => null,
                    'created_by' => User::whereHas('role', fn ($q) => $q->where('slug', 'lsg_officer'))->first()?->id,
                    'is_ceit_wide' => $eventData['is_ceit_wide'],
                    'type' => $eventData['type'],
                    'template' => $eventData['template'],
                    'require_timeout' => true,
                    'late_threshold_minutes' => 5,
                ]
            );
        }

        // Society events per society
        foreach ($societies as $slug => $society) {
            // Ongoing (all members)
            Event::firstOrCreate(
                ['title' => strtoupper($society->abbreviation).' Ongoing Assembly'],
                [
                    'description' => 'General assembly currently in session',
                    'start_at' => $now->copy()->subHour(),
                    'end_at' => $now->copy()->addHours(4),
                    'location' => 'Auditorium',
                    'attendance_mode' => 'hybrid',
                    'audience' => 'society_members',
                    'audience_notes' => null,
                    'status' => 'active',
                    'society_id' => $society->id,
                    'created_by' => $society->members()->first()?->id,
                    'is_ceit_wide' => false,
                    'type' => 'society',
                    'template' => 'Assembly',
                    'require_timeout' => true,
                    'late_threshold_minutes' => 5,
                ]
            );

            // Upcoming (officers only)
            Event::firstOrCreate(
                ['title' => strtoupper($society->abbreviation).' Officers Planning'],
                [
                    'description' => 'Officers planning meeting',
                    'start_at' => $now->copy()->addDays(2)->setTime(20, 0),
                    'end_at' => $now->copy()->addDays(2)->setTime(22, 0),
                    'location' => 'Conference Room',
                    'attendance_mode' => 'manual',
                    'audience' => 'society_officers',
                    'audience_notes' => null,
                    'status' => 'active',
                    'society_id' => $society->id,
                    'created_by' => $society->members()->first()?->id,
                    'is_ceit_wide' => false,
                    'type' => 'society',
                    'template' => 'Meeting',
                    'require_timeout' => true,
                    'late_threshold_minutes' => 5,
                ]
            );

            // Ended (all members)
            Event::firstOrCreate(
                ['title' => strtoupper($society->abbreviation).' Past Workshop'],
                [
                    'description' => 'Completed workshop for members',
                    'start_at' => $now->copy()->subDays(3)->setTime(14, 0),
                    'end_at' => $now->copy()->subDays(3)->setTime(16, 0),
                    'location' => 'Lab 1',
                    'attendance_mode' => 'qr',
                    'audience' => 'society_members',
                    'audience_notes' => null,
                    'status' => 'active',
                    'society_id' => $society->id,
                    'created_by' => $society->members()->first()?->id,
                    'is_ceit_wide' => false,
                    'type' => 'society',
                    'template' => 'Workshop',
                    'require_timeout' => true,
                    'late_threshold_minutes' => 5,
                ]
            );
        }
    }
}
