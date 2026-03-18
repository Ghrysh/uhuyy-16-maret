<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RumusKodeSeeder extends Seeder
{
    private function getJabatanId($keyword) 
    {
        return DB::table('ref_jabatan_satker')
            ->where('label_jabatan', 'ILIKE', "%{$keyword}%")
            ->orWhere('key_jabatan', 'ILIKE', "%{$keyword}%")
            ->value('id');
    }

    /**
     * Run the database seeds.
     */
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

            // 1. WAKIL REKTOR / WAKIL KETUA (Kode Dasar: 9)
            [
                'nama_rumus' => 'Setup Wakil Rektor / Wakil Ketua',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('Wakil Rektor'),
                'kode_awalan' => '9', 'is_auto_number' => true, 'digit_auto_number' => 1,
                'default_nama_satker' => 'Wakil Rektor Bidang', 'pola' => '[PARENT]9[INC:1]', 'is_applied' => true,
            ],

            // 2. DEKAN (Kode Dasar: 0)
            [
                'nama_rumus' => 'Setup Dekan',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('Dekan'),
                'kode_awalan' => '0', 'is_auto_number' => true, 'digit_auto_number' => 1,
                'default_nama_satker' => 'Fakultas', 'pola' => '[PARENT]0[INC:1]', 'is_applied' => true,
            ],

            // 3. WAKIL DEKAN (Kode Dasar: 9)
            [
                'nama_rumus' => 'Setup Wakil Dekan',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('Wakil Dekan'),
                'kode_awalan' => '9', 'is_auto_number' => true, 'digit_auto_number' => 1,
                'default_nama_satker' => 'Wakil Dekan Bidang', 'pola' => '[PARENT]9[INC:1]', 'is_applied' => true,
            ],

            // 4. KEPALA BIRO (Kode Dasar: 0)
            [
                'nama_rumus' => 'Setup Kepala Biro',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('Biro'),
                'kode_awalan' => '0', 'is_auto_number' => true, 'digit_auto_number' => 1,
                'default_nama_satker' => 'Biro', 'pola' => '[PARENT]0[INC:1]', 'is_applied' => true,
            ],

            // 5. KETUA LEMBAGA (Kode Dasar: 0)
            [
                'nama_rumus' => 'Setup Ketua Lembaga',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('Lembaga'),
                'kode_awalan' => '0', 'is_auto_number' => true, 'digit_auto_number' => 1,
                'default_nama_satker' => 'Lembaga', 'pola' => '[PARENT]0[INC:1]', 'is_applied' => true,
            ],

            // 6. KEPALA UPT (Kode Dasar: 0)
            [
                'nama_rumus' => 'Setup Kepala UPT',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('UPT'),
                'kode_awalan' => '0', 'is_auto_number' => true, 'digit_auto_number' => 1,
                'default_nama_satker' => 'UPT', 'pola' => '[PARENT]0[INC:1]', 'is_applied' => true,
            ],

            // 7. DIREKTUR PASCASARJANA (Kode Dasar: 0)
            [
                'nama_rumus' => 'Setup Direktur Pascasarjana',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('Pascasarjana'),
                'kode_awalan' => '0', 'is_auto_number' => true, 'digit_auto_number' => 1,
                'default_nama_satker' => 'Pascasarjana', 'pola' => '[PARENT]0[INC:1]', 'is_applied' => true,
            ],

            // 8. KEPALA BAGIAN TATA USAHA (Kode Dasar: 00 - FIX CODE)
            [
                'nama_rumus' => 'Setup Kabag Tata Usaha',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('Tata Usaha'),
                'kode_awalan' => '00', 'is_auto_number' => false, 'digit_auto_number' => null,
                'default_nama_satker' => 'Bagian Tata Usaha', 'pola' => '[PARENT]00', 'is_applied' => true,
            ],

            // 9. KEPALA BAGIAN SELAIN TU (Kode Dasar: 0)
            [
                'nama_rumus' => 'Setup Kepala Bagian (Non-TU)',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('Bagian'),
                'kode_awalan' => '0', 'is_auto_number' => true, 'digit_auto_number' => 1,
                'default_nama_satker' => 'Bagian', 'pola' => '[PARENT]0[INC:1]', 'is_applied' => true,
            ],

            // 10. KEPALA SUBBAGIAN TATA USAHA (Kode Dasar: 00 - FIX CODE)
            [
                'nama_rumus' => 'Setup Kasubbag Tata Usaha',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('Subbagian Tata Usaha'),
                'kode_awalan' => '00', 'is_auto_number' => false, 'digit_auto_number' => null,
                'default_nama_satker' => 'Subbagian Tata Usaha', 'pola' => '[PARENT]00', 'is_applied' => true,
            ],

            // 11. KEPALA SUBBAGIAN SELAIN TU (Kode Dasar: 0)
            [
                'nama_rumus' => 'Setup Kasubbag (Non-TU)',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('Subbagian'),
                'kode_awalan' => '0', 'is_auto_number' => true, 'digit_auto_number' => 1,
                'default_nama_satker' => 'Subbagian', 'pola' => '[PARENT]0[INC:1]', 'is_applied' => true,
            ],

            // 12. KETUA JURUSAN / KETUA PROGRAM STUDI (Kode Dasar: 0)
            [
                'nama_rumus' => 'Setup Ketua Jurusan/Prodi',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('Jurusan'),
                'kode_awalan' => '0', 'is_auto_number' => true, 'digit_auto_number' => 1,
                'default_nama_satker' => 'Jurusan', 'pola' => '[PARENT]0[INC:1]', 'is_applied' => true,
            ],

            // 13. SEKRETARIS JURUSAN / SEKRETARIS PRODI (Kode Dasar: 9)
            [
                'nama_rumus' => 'Setup Sekretaris Jurusan/Prodi',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('Sekretaris Jurusan'),
                'kode_awalan' => '9', 'is_auto_number' => true, 'digit_auto_number' => 1,
                'default_nama_satker' => 'Sekretaris', 'pola' => '[PARENT]9[INC:1]', 'is_applied' => true,
            ],

            // 14. TIDAK ADA JABATAN (Kode Dasar: 00 - FIX CODE)
            [
                'nama_rumus' => 'Setup Tidak Ada Jabatan',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('Tidak ada Jabatan'),
                'kode_awalan' => '00', 'is_auto_number' => false, 'digit_auto_number' => null,
                'default_nama_satker' => 'Unit Pelaksana', 'pola' => '[PARENT]00', 'is_applied' => true,
            ],

            // 15. SUB TIM KERJA / SUB KELOMPOK KERJA (Kode Dasar: 00 - FIX CODE)
            [
                'nama_rumus' => 'Setup Sub Tim Kerja',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('Sub Tim'),
                'kode_awalan' => '00', 'is_auto_number' => false, 'digit_auto_number' => null,
                'default_nama_satker' => 'Sub Tim Kerja', 'pola' => '[PARENT]00', 'is_applied' => true,
            ],

            // 16. TIM KERJA / KELOMPOK KERJA (Kode Dasar: 00 - FIX CODE)
            [
                'nama_rumus' => 'Setup Tim Kerja',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('Tim Kerja'),
                'kode_awalan' => '00', 'is_auto_number' => false, 'digit_auto_number' => null,
                'default_nama_satker' => 'Tim Kerja', 'pola' => '[PARENT]00', 'is_applied' => true,
            ],

            // 17. SEKRETARIS LEMBAGA (Kode Dasar: 0)
            [
                'nama_rumus' => 'Setup Sekretaris Lembaga',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('Sekretaris Lembaga'),
                'kode_awalan' => '0', 'is_auto_number' => true, 'digit_auto_number' => 1,
                'default_nama_satker' => 'Sekretariat Lembaga', 'pola' => '[PARENT]0[INC:1]', 'is_applied' => true,
            ],

            // 18. KEPALA PUSAT (Kode Dasar: 0)
            [
                'nama_rumus' => 'Setup Kepala Pusat',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('Pusat'),
                'kode_awalan' => '0', 'is_auto_number' => true, 'digit_auto_number' => 1,
                'default_nama_satker' => 'Pusat', 'pola' => '[PARENT]0[INC:1]', 'is_applied' => true,
            ],

            // 19. SEKRETARIS PUSAT (Kode Dasar: 0)
            [
                'nama_rumus' => 'Setup Sekretaris Pusat',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('Sekretaris Pusat'),
                'kode_awalan' => '0', 'is_auto_number' => true, 'digit_auto_number' => 1,
                'default_nama_satker' => 'Sekretariat Pusat', 'pola' => '[PARENT]0[INC:1]', 'is_applied' => true,
            ],

            // 20. WAKIL DIREKTUR PASCASARJANA (Kode Dasar: 0)
            [
                'nama_rumus' => 'Setup Wakil Direktur Pascasarjana',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('Wakil Direktur'),
                'kode_awalan' => '0', 'is_auto_number' => true, 'digit_auto_number' => 1,
                'default_nama_satker' => 'Wakil Direktur', 'pola' => '[PARENT]0[INC:1]', 'is_applied' => true,
            ],

            // 21. KOORDINATOR PUSAT (Kode Dasar: 0)
            [
                'nama_rumus' => 'Setup Koordinator Pusat',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('Koordinator'),
                'kode_awalan' => '0', 'is_auto_number' => true, 'digit_auto_number' => 1,
                'default_nama_satker' => 'Koordinator Pusat', 'pola' => '[PARENT]0[INC:1]', 'is_applied' => true,
            ],

            // 22. KEPALA SATUAN PENGAWAS INTERNAL (SPI) (Kode Dasar: 0)
            [
                'nama_rumus' => 'Setup Kepala SPI',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('Pengawas'),
                'kode_awalan' => '0', 'is_auto_number' => true, 'digit_auto_number' => 1,
                'default_nama_satker' => 'Satuan Pengawas Internal', 'pola' => '[PARENT]0[INC:1]', 'is_applied' => true,
            ],

            // 23. SEKRETARIS SATUAN PENGAWAS INTERNAL (SPI) (Kode Dasar: 0)
            [
                'nama_rumus' => 'Setup Sekretaris SPI',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('Sekretaris SPI'),
                'kode_awalan' => '0', 'is_auto_number' => true, 'digit_auto_number' => 1,
                'default_nama_satker' => 'Sekretariat SPI', 'pola' => '[PARENT]0[INC:1]', 'is_applied' => true,
            ],

            // 24. KEPALA UNIT PENDUKUNG AKADEMI (UPA) (Kode Dasar: 0)
            [
                'nama_rumus' => 'Setup Kepala UPA',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('UPA'),
                'kode_awalan' => '0', 'is_auto_number' => true, 'digit_auto_number' => 1,
                'default_nama_satker' => 'Unit Pendukung Akademi', 'pola' => '[PARENT]0[INC:1]', 'is_applied' => true,
            ],

            // 25. KEPALA KLINIK (Kode Dasar: 0)
            [
                'nama_rumus' => 'Setup Kepala Klinik',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null,
                'ref_jabatan_satker_id' => $this->getJabatanId('Klinik'),
                'kode_awalan' => '0', 'is_auto_number' => true, 'digit_auto_number' => 1,
                'default_nama_satker' => 'Klinik', 'pola' => '[PARENT]0[INC:1]', 'is_applied' => true,
            ],
        ];

        DB::table('rumus_kodes')->truncate();

        foreach ($rumusList as $rumus) {
            $rumus['created_at'] = $now;
            $rumus['updated_at'] = $now;
            DB::table('rumus_kodes')->insert($rumus);
        }
    }
}