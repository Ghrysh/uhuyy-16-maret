<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RumusKodeSeeder extends Seeder
{
    private function getJabatanId($keyword) 
    {
        return DB::table('ref_jabatan_satker')
            ->where('key_jabatan', 'ILIKE', "%{$keyword}%")
            ->orWhere('label_jabatan', 'ILIKE', "%{$keyword}%")
            ->value('id');
    }

    public function run(): void
    {
        $now = now();

        $rumusList = [
            // ==========================================
            // 0. DEFAULT MUTLAK (JIKA TIDAK PILIH JABATAN)
            // ==========================================
            [
                'nama_rumus' => 'Default Sistem (Urut 2 Digit)',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null,
                'kode_awalan' => '', 'is_auto_number' => true, 'digit_auto_number' => 2,
                'default_nama_satker' => null, 'pola' => '[PARENT][INC:2]', 
                'is_applied' => true,
            ],

            // ==========================================
            // A. JABATAN STRUKTURAL KHUSUS & PATEN
            // ==========================================
            [
                'nama_rumus' => 'Setup Tata Usaha (Paten 01)',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('tata_usaha') ?? $this->getJabatanId('Tata Usaha'),
                'kode_awalan' => '01', 'is_auto_number' => false, 'digit_auto_number' => null,
                'default_nama_satker' => 'Tata Usaha', 'pola' => '[PARENT]01', 'is_applied' => true,
            ],
            [
                'nama_rumus' => 'Setup Seksi Pend. Madrasah (Paten 02)',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('pendidikan_madrasah') ?? $this->getJabatanId('Madrasah'),
                'kode_awalan' => '02', 'is_auto_number' => false, 'digit_auto_number' => null,
                'default_nama_satker' => 'Seksi Pendidikan Madrasah', 'pola' => '[PARENT]02', 'is_applied' => true,
            ],
            [
                'nama_rumus' => 'Setup Seksi Bimas Islam (Paten 03)',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('bimas_islam') ?? $this->getJabatanId('Bimbingan Masyarakat'),
                'kode_awalan' => '03', 'is_auto_number' => false, 'digit_auto_number' => null,
                'default_nama_satker' => 'Seksi Bimbingan Masyarakat Islam', 'pola' => '[PARENT]03', 'is_applied' => true,
            ],

            // ==========================================
            // B. JABATAN TUGAS TAMBAHAN PTKN (KODE SAKTI '9')
            // ==========================================
            [
                'nama_rumus' => 'Setup Wakil Rektor',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('wakil_rektor') ?? $this->getJabatanId('Wakil Rektor'),
                'kode_awalan' => '9', 'is_auto_number' => true, 'digit_auto_number' => 1,
                'default_nama_satker' => 'Wakil Rektor', 'pola' => '[PARENT]9[INC:1]', 'is_applied' => true,
            ],
            [
                'nama_rumus' => 'Setup Dekan',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('dekan') ?? $this->getJabatanId('Dekan'), 
                'kode_awalan' => '9', 'is_auto_number' => true, 'digit_auto_number' => 2, 
                'default_nama_satker' => 'Fakultas', 'pola' => '[PARENT]9[INC:2]', 'is_applied' => true,
            ],
            [
                'nama_rumus' => 'Setup Ketua Lembaga',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('lembaga') ?? $this->getJabatanId('Lembaga'),
                'kode_awalan' => '9', 'is_auto_number' => true, 'digit_auto_number' => 2, 
                'default_nama_satker' => 'Lembaga', 'pola' => '[PARENT]9[INC:2]', 'is_applied' => true,
            ],
            [
                'nama_rumus' => 'Setup Wakil Dekan',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('wakil_dekan') ?? $this->getJabatanId('Wakil Dekan'),
                'kode_awalan' => '', 'is_auto_number' => true, 'digit_auto_number' => 1, 
                'default_nama_satker' => 'Wakil Dekan', 'pola' => '[PARENT][INC:1]', 'is_applied' => true,
            ],
            [
                'nama_rumus' => 'Setup Ketua Jurusan',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('jurusan') ?? $this->getJabatanId('Jurusan'),
                'kode_awalan' => '', 'is_auto_number' => true, 'digit_auto_number' => 2, 
                'default_nama_satker' => 'Jurusan', 'pola' => '[PARENT][INC:2]', 'is_applied' => true,
            ],
            [
                'nama_rumus' => 'Setup Ketua Prodi',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('program_studi') ?? $this->getJabatanId('Program Studi'),
                'kode_awalan' => '00', 'is_auto_number' => true, 'digit_auto_number' => 2, 
                'default_nama_satker' => 'Program Studi', 'pola' => '[PARENT]00[INC:2]', 'is_applied' => true,
            ],

            // ==========================================
            // C. LAIN-LAIN (TIM KERJA & NON-JABATAN)
            // ==========================================
            [
                'nama_rumus' => 'Setup Tim Kerja (Paten 98)',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('tim_kerja') ?? $this->getJabatanId('Tim Kerja'),
                'kode_awalan' => '98', 'is_auto_number' => false, 'digit_auto_number' => null,
                'default_nama_satker' => 'Tim Kerja', 'pola' => '[PARENT]98', 'is_applied' => true,
            ],
            [
                'nama_rumus' => 'Setup Tidak Ada Jabatan (Paten 99)',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('tidak_ada') ?? $this->getJabatanId('Tidak'), // <-- KUNCI UTAMANYA DI SINI
                'kode_awalan' => '99', 'is_auto_number' => false, 'digit_auto_number' => null,
                'default_nama_satker' => 'Unit Pelaksana', 'pola' => '[PARENT]99', 'is_applied' => true,
            ],
        ];

        DB::table('rumus_kodes')->truncate();

        $berhasil = 0;
        $gagal = 0;

        foreach ($rumusList as $rumus) {

            if ($rumus['nama_rumus'] !== 'Default Sistem (Urut 2 Digit)' && is_null($rumus['ref_jabatan_satker_id'])) {
                $this->command->warn("⚠️  DILEWATI: {$rumus['nama_rumus']} (Jabatan tidak ditemukan di DB)");
                $gagal++;
                continue; 
            }

            $rumus['created_at'] = $now;
            $rumus['updated_at'] = $now;
            DB::table('rumus_kodes')->insert($rumus);
            $berhasil++;
        }

        $this->command->info("✅ Selesai! Berhasil membuat {$berhasil} Rumus. Gagal: {$gagal}");
    }
}