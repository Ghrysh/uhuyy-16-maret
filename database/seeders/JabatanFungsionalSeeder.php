<?php

namespace Database\Seeders;

use App\Models\JabatanFungsional;
use Illuminate\Database\Seeder;

class JabatanFungsionalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jabatans = [
            'Pemula',
            'Terampil',
            'Mahir',
            'Penyelia',
            'Ahli Pertama',
            'Ahli Muda',
            'Ahli Madya',
            'Ahli Utama',
        ];

        $startCode = 11;

        foreach ($jabatans as $index => $name) {
            JabatanFungsional::create([
                'kode' => $startCode + $index, // Menghasilkan 11, 12, 13, dst.
                'name' => $name,
            ]);
        }
    }
}