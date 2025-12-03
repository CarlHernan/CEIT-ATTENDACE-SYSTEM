<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Society;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'Student', 'slug' => 'student'],
            ['name' => 'Society Officer', 'slug' => 'officer'],
            ['name' => 'CEIT-LSG Officer', 'slug' => 'lsg_officer'],
            ['name' => 'Admin', 'slug' => 'admin'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role['slug']], $role);
        }

        $societies = [
            ['name' => 'Philippine Society of Information Technology Students', 'slug' => 'psits', 'abbreviation' => 'PSITS'],
            ['name' => 'Philippine Institute of Civil Engineers', 'slug' => 'pice', 'abbreviation' => 'PICE'],
            ['name' => 'Institute of Computer Engineers of the Philippines', 'slug' => 'icpep', 'abbreviation' => 'ICPEP'],
            ['name' => 'Institute of Electronics Engineers of the Philippines', 'slug' => 'jiecep', 'abbreviation' => 'JIECEP'],
            ['name' => 'Philippine Society of Agricultural and Biosystems Engineers', 'slug' => 'psabe', 'abbreviation' => 'PSABE'],
        ];

        foreach ($societies as $society) {
            Society::firstOrCreate(['slug' => $society['slug']], $society);
        }

        // Seed elevated access accounts (credentials for initial testing)
        $adminRole = Role::where('slug', 'admin')->first();
        $officerRole = Role::where('slug', 'officer')->first();
        $lsgRole = Role::where('slug', 'lsg_officer')->first();
        $psits = Society::where('slug', 'psits')->first();
        $pice = Society::where('slug', 'pice')->first();
        $icpep = Society::where('slug', 'icpep')->first();
        $jiecep = Society::where('slug', 'jiecep')->first();
        $psabe = Society::where('slug', 'psabe')->first();

        $admin = User::firstOrCreate(
            ['email' => 'admin@ceit.local'],
            [
                'name' => 'CEIT Admin',
                'id_number' => 'ADMIN-0001',
                'course' => 'CEIT',
                'year_level' => 'N/A',
                'department' => 'CEIT',
                'email_verified_at' => now(),
                'qr_raw_text' => null,
                'role_id' => $adminRole->id ?? null,
                'password' => Hash::make('password'),
            ]
        );

        $officer = User::firstOrCreate(
            ['email' => 'officer@ceit.local'],
            [
                'name' => 'PSITS Soceity Officer',
                'id_number' => 'PSITS-0001',
                'course' => 'BSIT',
                'year_level' => '3',
                'department' => 'CEIT',
                'email_verified_at' => now(),
                'qr_raw_text' => null,
                'role_id' => $officerRole->id ?? null,
                'is_society_officer' => true,
                'password' => Hash::make('password'),
            ]
        );

        $lsgOfficer = User::firstOrCreate(
            ['email' => 'lsg@ceit.local'],
            [
                'name' => 'CEIT LSG Officer',
                'id_number' => 'LSG-0001',
                'course' => 'BSIT',
                'year_level' => '4',
                'department' => 'CEIT',
                'email_verified_at' => now(),
                'qr_raw_text' => null,
                'role_id' => $lsgRole->id ?? null,
                'is_lsg_officer' => true,
                'password' => Hash::make('password'),
            ]
        );

        if ($psits) {
            $officer?->societies()->syncWithoutDetaching([$psits->id => ['position' => 'Officer']]);
        }

        // Officers per society
        $officersBySociety = [
            ['society' => $psits, 'name' => 'PSITS Officer', 'email' => 'officer.psits@ceit.local', 'id_number' => 'PSITS-OFF-001', 'course' => 'BSIT'],
            ['society' => $pice, 'name' => 'PICE Officer', 'email' => 'officer.pice@ceit.local', 'id_number' => 'PICE-OFF-001', 'course' => 'BSCE'],
            ['society' => $icpep, 'name' => 'ICPEP Officer', 'email' => 'officer.icpep@ceit.local', 'id_number' => 'ICPEP-OFF-001', 'course' => 'BSCpE'],
            ['society' => $jiecep, 'name' => 'JIECEP Officer', 'email' => 'officer.jiecep@ceit.local', 'id_number' => 'JIECEP-OFF-001', 'course' => 'BSECE'],
            ['society' => $psabe, 'name' => 'PSABE Officer', 'email' => 'officer.psabe@ceit.local', 'id_number' => 'PSABE-OFF-001', 'course' => 'BSABE'],
        ];

        foreach ($officersBySociety as $data) {
            if (! $data['society']) {
                continue;
            }
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'id_number' => $data['id_number'],
                    'course' => $data['course'],
                    'year_level' => '3',
                    'department' => 'CEIT',
                    'email_verified_at' => now(),
                    'qr_raw_text' => null,
                    'role_id' => $officerRole->id ?? null,
                    'is_society_officer' => true,
                    'password' => Hash::make('password'),
                ]
            );
            $user->societies()->syncWithoutDetaching([$data['society']->id => ['position' => 'Officer']]);
        }

        // Example student factory user
        User::factory()->create([
            'name' => 'Sample Student',
            'email' => 'student@ceit.local',
            'email_verified_at' => now(),
        ]);

        // Specific seeded students
        if ($psits) {
            $carl = User::firstOrCreate(
                ['email' => 'cjcarlpactaoin@gmail.com'],
                [
                    'name' => 'Carl John H. Pactao-in',
                    'id_number' => '23-72634',
                    'course' => 'BSInfoSys',
                    'section' => 'A',
                    'year_level' => '3',
                    'department' => 'CEIT',
                    'qr_raw_text' => 'CARL JOHN H. PACTAO-IN,BSInfoSys,,',
                    'role_id' => Role::where('slug', 'student')->first()?->id,
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]
            );
            $carl?->societies()->syncWithoutDetaching([$psits->id => ['position' => 'Member']]);
        }

        if ($icpep) {
            $john = User::firstOrCreate(
                ['email' => 'john.chavez@example.com'],
                [
                    'name' => 'John Erick G. Chavez',
                    'id_number' => '23-62569',
                    'course' => 'BSCpE',
                    'section' => 'A',
                    'year_level' => '3',
                    'department' => 'CEIT',
                    'qr_raw_text' => 'JOHN ERICK G. CHAVEZ,BSCpE,,',
                    'role_id' => Role::where('slug', 'student')->first()?->id,
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]
            );
            $john?->societies()->syncWithoutDetaching([$icpep->id => ['position' => 'Member']]);
        }

        $this->call(EventSeeder::class);
    }
}
