<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\MRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSuperAdminSeeder extends Seeder
{
    public function run(): void
    {

        // 2. Buat User baru dengan UUID
        $userId = (string) Str::uuid();
        
        DB::table('users')->insert([
            'id' => $userId,
            'name' => 'Administrator Utama',
            'email' => 'admin@kemenag.go.id',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
            'satker_id' => null,
            'nip' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Hubungkan User ke Role di tabel user_roles
        DB::table('user_roles')->insert([
            'user_id' => $userId,
            'role_id' => 1,
        ]);
    }
}