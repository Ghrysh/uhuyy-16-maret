<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JabatanSatkerSeeder extends Seeder {
    public function run(): void {
        // --- 1. JABATAN UMUM (Wilayah NULL) ---
        $jabatanUmum = [
            ['id' => Str::uuid(), 'key_jabatan' => 'manajerial', 'label_jabatan' => 'Manajerial', 'kode_dasar' => null, 'tingkat_wilayah_id' => null],
            ['id' => Str::uuid(), 'key_jabatan' => 'jabatan_fungsional', 'label_jabatan' => 'Jabatan Fungsional', 'kode_dasar' => null, 'tingkat_wilayah_id' => null],
            ['id' => Str::uuid(), 'key_jabatan' => 'pelaksana', 'label_jabatan' => 'Pelaksana', 'kode_dasar' => '7', 'tingkat_wilayah_id' => null],
            ['id' => Str::uuid(), 'key_jabatan' => 'tidak_ada', 'label_jabatan' => 'Tidak ada Jabatan', 'kode_dasar' => '00', 'tingkat_wilayah_id' => null],
        ];
        DB::table('ref_jabatan_satker')->insert($jabatanUmum);

        // --- 2. PTKN (Tingkat Wilayah ID: 4) ---
        $ptknData = [
            ['id' => Str::uuid(), 'tingkat_wilayah_id' => 4, 'key_jabatan' => 'wakil_rektor', 'label_jabatan' => 'Wakil Rektor', 'kode_dasar' => '91', 'is_increment' => true],
            ['id' => Str::uuid(), 'tingkat_wilayah_id' => 4, 'key_jabatan' => 'dekan', 'label_jabatan' => 'Dekan', 'kode_dasar' => '901', 'is_increment' => false],
            ['id' => Str::uuid(), 'tingkat_wilayah_id' => 4, 'key_jabatan' => 'wakil_dekan', 'label_jabatan' => 'Wakil Dekan', 'kode_dasar' => '9011', 'is_increment' => true],
            ['id' => Str::uuid(), 'tingkat_wilayah_id' => 4, 'key_jabatan' => 'ketua_jurusan', 'label_jabatan' => 'Ketua Jurusan', 'kode_dasar' => '90101', 'is_increment' => false],
            ['id' => Str::uuid(), 'tingkat_wilayah_id' => 4, 'key_jabatan' => 'wakil_jurusan', 'label_jabatan' => 'Wakil Jurusan', 'kode_dasar' => '901011', 'is_increment' => true],
            ['id' => Str::uuid(), 'tingkat_wilayah_id' => 4, 'key_jabatan' => 'kepala_prodi', 'label_jabatan' => 'Ketua Prodi', 'kode_dasar' => '01', 'is_increment' => false],
            ['id' => Str::uuid(), 'tingkat_wilayah_id' => 4, 'key_jabatan' => 'wakil_kepala_prodi', 'label_jabatan' => 'Seketaris Kepala Prodi', 'kode_dasar' => '90101011', 'is_increment' => true],
            ['id' => Str::uuid(), 'tingkat_wilayah_id' => 4, 'key_jabatan' => 'lembaga', 'label_jabatan' => 'Lembaga', 'kode_dasar' => '921', 'is_increment' => false],
            ['id' => Str::uuid(), 'tingkat_wilayah_id' => 4, 'key_jabatan' => 'sekretaris_lembaga', 'label_jabatan' => 'Sekretaris Lembaga', 'kode_dasar' => '9211', 'is_increment' => true],
            ['id' => Str::uuid(), 'tingkat_wilayah_id' => 4, 'key_jabatan' => 'pusat', 'label_jabatan' => 'Pusat', 'kode_dasar' => '92101', 'is_increment' => false],
            ['id' => Str::uuid(), 'tingkat_wilayah_id' => 4, 'key_jabatan' => 'sekretaris_kepala_pusat', 'label_jabatan' => 'Sekretaris Kepala Pusat', 'kode_dasar' => '921011', 'is_increment' => true],
        ];
        DB::table('ref_jabatan_satker')->insert($ptknData);

        // --- 3. PROVINSI (ID: 2) & HIERARKI KABKOTA DI DALAMNYA ---
        
        // Level 1: Jabatan di Kanwil
        $idKanwil = Str::uuid();
        DB::table('ref_jabatan_satker')->insert([
            'id' => $idKanwil, 'tingkat_wilayah_id' => 2, 'key_jabatan' => 'jabatan_kanwil', 'label_jabatan' => 'Jabatan di Kanwil', 'kode_dasar' => null
        ]);

        // Level 1: Jabatan di Kota/Kab (Bernaung di Provinsi)
        $idKabKota = Str::uuid();
        DB::table('ref_jabatan_satker')->insert([
            'id' => $idKabKota, 'tingkat_wilayah_id' => 2, 'key_jabatan' => 'jabatan_kotakab', 'label_jabatan' => 'Jabatan di Kota/Kab', 'kode_dasar' => '21'
        ]);

        // Level 2: Di bawah Kanwil
        DB::table('ref_jabatan_satker')->insert([
            ['id' => Str::uuid(), 'parent_id' => $idKanwil, 'key_jabatan' => 'tu_kanwil', 'label_jabatan' => 'Tata Usaha', 'kode_dasar' => '01'],
            ['id' => Str::uuid(), 'parent_id' => $idKanwil, 'key_jabatan' => 'non_tu_kanwil', 'label_jabatan' => 'Non Tata Usaha', 'kode_dasar' => '04'],
        ]);

        // Level 2: Di bawah Kota/Kab
        $idMadrasah = Str::uuid();
        DB::table('ref_jabatan_satker')->insert([
            ['id' => Str::uuid(), 'parent_id' => $idKabKota, 'key_jabatan' => 'tu_kab', 'label_jabatan' => 'Tata Usaha', 'kode_dasar' => '01'],
            ['id' => Str::uuid(), 'parent_id' => $idKabKota, 'key_jabatan' => 'bimas_islam', 'label_jabatan' => 'Bimas Islam', 'kode_dasar' => '03'],
            ['id' => $idMadrasah, 'parent_id' => $idKabKota, 'key_jabatan' => 'madrasah', 'label_jabatan' => 'Madrasah', 'kode_dasar' => null],
        ]);

        // Level 3: Di bawah Madrasah
        DB::table('ref_jabatan_satker')->insert([
            ['id' => Str::uuid(), 'parent_id' => $idMadrasah, 'key_jabatan' => 'min', 'label_jabatan' => 'Madrasah Ibtidaiyah Negeri', 'kode_dasar' => '02'],
            ['id' => Str::uuid(), 'parent_id' => $idMadrasah, 'key_jabatan' => 'mtsn', 'label_jabatan' => 'Madrasah Tsanawiyah Negeri', 'kode_dasar' => '02'],
            ['id' => Str::uuid(), 'parent_id' => $idMadrasah, 'key_jabatan' => 'man', 'label_jabatan' => 'Madrasah Aliyah Negeri', 'kode_dasar' => '02'],
        ]);
    }
}