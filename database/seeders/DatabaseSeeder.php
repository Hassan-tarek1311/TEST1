<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Organization;
use App\Models\User;
use App\Models\Project;
use App\Models\TimeLog;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ===========================
        // 1. إنشاء شركة تجريبية
        // ===========================
        $org = Organization::firstOrCreate(
            ['email' => 'hr@eais.com'],
            ['name'  => 'EAIS Tech']
        );

        // ===========================
        // 2. إنشاء المدير
        // ===========================
        $admin = User::firstOrCreate(
            ['email' => 'admin@eais.com'],
            [
                'organization_id' => $org->id,
                'name'            => 'Admin User',
                'password'        => Hash::make('password'),
                'role'            => 'admin',
            ]
        );

        // ===========================
        // 3. إنشاء 3 موظفين
        // ===========================
        $employees = collect([
            ['name' => 'Ahmed Ali',   'email' => 'ahmed@eais.com'],
            ['name' => 'Sara Hassan', 'email' => 'sara@eais.com'],
            ['name' => 'Omar Khalid', 'email' => 'omar@eais.com'],
        ])->map(fn($data) => User::firstOrCreate(
            ['email' => $data['email']],
            [
                'organization_id' => $org->id,
                'name'            => $data['name'],
                'password'        => Hash::make('password'),
                'role'            => 'employee',
            ]
        ));

        // ===========================
        // 4. إنشاء مشاريع
        // ===========================
        $projects = collect([
            ['name' => 'Website Redesign',   'status' => 'active'],
            ['name' => 'Mobile App v2',      'status' => 'active'],
            ['name' => 'API Integration',    'status' => 'completed'],
        ])->map(fn($data) => Project::firstOrCreate(
            ['name' => $data['name'], 'organization_id' => $org->id],
            [
                'description'     => "Project: {$data['name']}",
                'status'          => $data['status'],
                'deadline'        => Carbon::now()->addDays(rand(10, 60)),
            ]
        ));

        // ===========================
        // 5. إنشاء سجلات وقت واقعية لآخر 7 أيام
        // ===========================
        $allUsers = $employees->push($admin);

        foreach ($allUsers as $user) {
            for ($day = 6; $day >= 0; $day--) {
                // كل موظف يشتغل session أو اتنين في اليوم
                $sessions = rand(1, 2);
                $startHour = 9;

                for ($s = 0; $s < $sessions; $s++) {
                    $started = Carbon::now()->subDays($day)->setHour($startHour)->setMinute(0);
                    $duration = rand(90, 240); // من 90 دقيقة لـ 4 ساعات
                    $ended = $started->copy()->addMinutes($duration);

                    TimeLog::create([
                        'user_id'          => $user->id,
                        'project_id'       => $projects->random()->id,
                        'started_at'       => $started,
                        'ended_at'         => $ended,
                        'duration_minutes' => $duration,
                        'notes'            => "Work session {$s}",
                    ]);

                    $startHour += 3; // الـ session التانية بعد 3 ساعات
                }
            }
        }

        $this->command->info('');
        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('');
        $this->command->info('Login credentials:');
        $this->command->info('  Admin:    admin@eais.com  / password');
        $this->command->info('  Employee: ahmed@eais.com / password');
        $this->command->info('');
    }
}
