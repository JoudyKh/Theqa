<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Constants\Constants;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
    //Role::truncate() ;

        $roles = [
            [
                'name' => Constants::SUPER_ADMIN_ROLE,
                'guard_name' => 'api',
            ],
            [
                'name' => Constants::ADMIN_ROLE,
                'guard_name' => 'api',
            ],
            [
                'name' => Constants::STUDENT_ROLE,
                'guard_name' => 'api',
            ],
            [
                'name' => Constants::TEACHER_ROLE,
                'guard_name' => 'api',
            ]
        ];
        
        Role::insert($roles);

        $admin = User::create([
            'username' => 'admin',
            'email' => 'yosofbayan75@gmail.com',
            'first_name' => 'yosof',
            'last_name' => 'bayan',
            'phone_number' => '+963967213544',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('12345678'),
            'is_active' => '1',
        ]);
        
        $admin->assignRole(Constants::SUPER_ADMIN_ROLE);


        $student = User::create([
            'username' => 'student',
            'email' => 'student@gmail.com',
            'first_name' => 'student',
            'last_name' => 'student',
            'phone_number' => '+963967213544',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('12345678'),
            'is_active' => '1',
        ]);

        $student->assignRole(Constants::STUDENT_ROLE);
    }
}
