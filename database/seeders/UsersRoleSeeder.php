<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersRoleSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Akun Super Admin',
                'email' => 'superadmin@kemenag.go.id',
                'role_id' => 1,
            ],
            [
                'name' => 'Akun Admin Pusat',
                'email' => 'adminpusat@kemenag.go.id',
                'role_id' => 2,
            ],
            [
                'name' => 'Akun Admin Wilayah',
                'email' => 'adminwilayah@kemenag.go.id',
                'role_id' => 3,
            ],
            [
                'name' => 'Akun Admin Satker',
                'email' => 'adminsatker@kemenag.go.id',
                'role_id' => 4,
            ],
            [
                'name' => 'Akun Viewer',
                'email' => 'viewer@kemenag.go.id',
                'role_id' => 5,
            ],
        ];

        foreach ($users as $userData) {
            $userId = (string) Str::uuid();

            DB::table('users')->insert([
                'id' => $userId,
                'name' => $userData['name'],
                'email' => $userData['email'],
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'satker_id' => null,
                'nip' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => $userData['role_id'],
            ]);
        }
    }
}
