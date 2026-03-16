<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'id' => Str::uuid(),
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@example.com',
                'password' => Hash::make('password'),
                'nip' => '198001012006041001',
                'satker_id' => 1,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@example.com',
                'password' => Hash::make('password'),
                'nip' => '198203152007011002',
                'satker_id' => 1,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@example.com',
                'password' => Hash::make('password'),
                'nip' => '198507102008031003',
                'satker_id' => 1,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Dewi Anggraini',
                'email' => 'dewi.anggraini@example.com',
                'password' => Hash::make('password'),
                'nip' => '198611212009041004',
                'satker_id' => 1,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Rizky Pratama',
                'email' => 'rizky.pratama@example.com',
                'password' => Hash::make('password'),
                'nip' => '198904302010021005',
                'satker_id' => 1,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Lina Marlina',
                'email' => 'lina.marlina@example.com',
                'password' => Hash::make('password'),
                'nip' => '199102182011031006',
                'satker_id' => 1,
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
