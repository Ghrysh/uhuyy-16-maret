<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RumusKodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Kosongkan tabel rumus_kodes (Hapus semua rumus lama)
        DB::table('rumus_kodes')->truncate();

        // 2. Helper untuk mencari ID Jabatan secara dinamis agar tidak error (berdasarkan kata kunci)
        $getIdJabatan = function($keyword) {
            $jabatan = DB::table('ref_jabatan_satker')
                ->where('key_jabatan', 'ILIKE', "%{$keyword}%")
                ->orWhere('label_jabatan', 'ILIKE', "%{$keyword}%")
                ->first();
            return $jabatan ? $jabatan->id : null;
        };

        // Asumsi ID Tingkat Wilayah (Berdasarkan standarisasi database sebelumnya)
        $Pusat = 1; 
        $Kanwil = 2; 
        $KabKota = 3; 
        $PTKN = 4;

        // Ambil ID Eselon
        $Es1 = DB::table('m_jenis_satker')->where('nama', 'ILIKE', '%Eselon 1%')->value('id') ?? 1;
        $Es2 = DB::table('m_jenis_satker')->where('nama', 'ILIKE', '%Eselon 2%')->value('id') ?? 2;
        $Es3 = DB::table('m_jenis_satker')->where('nama', 'ILIKE', '%Eselon 3%')->value('id') ?? 3;
        $Es4 = DB::table('m_jenis_satker')->where('nama', 'ILIKE', '%Eselon 4%')->value('id') ?? 4;

        // 3. Array Data Rumus Berdasarkan Dokumen 0. Kode Satuan Kerja_RAPI.docx
        $daftarRumus = [
            // ==========================================
            // I. ESELON 1 (KODE DASAR AWAL)
            // ==========================================
            [
                'nama_rumus' => 'Eselon 1 Pusat',
                'tingkat_wilayah_id' => $Pusat, 'jenis_satker_id' => $Es1, 'ref_jabatan_satker_id' => null,
                'pola' => '[INC:2, START:01]' // Mulai dari 01, 02, 03...
            ],
            [
                'nama_rumus' => 'Eselon 1 PTKN (Tugas Tambahan Rektor/Ketua)',
                'tingkat_wilayah_id' => $PTKN, 'jenis_satker_id' => $Es1, 'ref_jabatan_satker_id' => null,
                'pola' => '[INC:2, START:21]' // Mulai dari 21, 22, 23...
            ],

            // ==========================================
            // II. ESELON 2 (4 & 5 DIGIT)
            // ==========================================
            [
                'nama_rumus' => 'Eselon 2 Pusat',
                'tingkat_wilayah_id' => $Pusat, 'jenis_satker_id' => $Es2, 'ref_jabatan_satker_id' => null,
                'pola' => '[PARENT][INC:2, START:01]' // 0101, 0102...
            ],
            [
                'nama_rumus' => 'Eselon 2 Kanwil (Provinsi)',
                'tingkat_wilayah_id' => $Kanwil, 'jenis_satker_id' => $Es2, 'ref_jabatan_satker_id' => null,
                'pola' => '[PARENT][INC:2, START:21]' // 0121, 0122...
            ],
            [
                'nama_rumus' => 'Wakil Rektor (PTKN)',
                'tingkat_wilayah_id' => $PTKN, 'jenis_satker_id' => $Es2, 'ref_jabatan_satker_id' => $getIdJabatan('wakil_rektor'),
                'pola' => '[PARENT]9[INC:1, START:1]' // 2191, 2192...
            ],
            [
                'nama_rumus' => 'Dekan (PTKN)',
                'tingkat_wilayah_id' => $PTKN, 'jenis_satker_id' => $Es2, 'ref_jabatan_satker_id' => $getIdJabatan('dekan'),
                'pola' => '[PARENT]9[INC:2, START:01]' // 21901, 21902...
            ],
            [
                'nama_rumus' => 'Direktur Pascasarjana (PTKN)',
                'tingkat_wilayah_id' => $PTKN, 'jenis_satker_id' => $Es2, 'ref_jabatan_satker_id' => $getIdJabatan('pascasarjana'),
                'pola' => '[PARENT]9[INC:2, START:51]' // 21951, 21952...
            ],
            [
                'nama_rumus' => 'SPI dan Lembaga (PTKN)',
                'tingkat_wilayah_id' => $PTKN, 'jenis_satker_id' => $Es2, 'ref_jabatan_satker_id' => $getIdJabatan('spi'),
                'pola' => '[PARENT]9[INC:2, START:61]' // 21961, 21962...
            ],

            // ==========================================
            // III. ESELON 3 (6 DIGIT)
            // ==========================================
            [
                'nama_rumus' => 'Eselon 3 Pusat',
                'tingkat_wilayah_id' => $Pusat, 'jenis_satker_id' => $Es3, 'ref_jabatan_satker_id' => null,
                'pola' => '[PARENT][INC:2, START:01]' 
            ],
            [
                'nama_rumus' => 'Kemenag Kab/Kota (Eselon 3 Daerah)',
                'tingkat_wilayah_id' => $KabKota, 'jenis_satker_id' => $Es3, 'ref_jabatan_satker_id' => null,
                'pola' => '[PARENT][INC:2, START:01]' 
            ],
            [
                'nama_rumus' => 'Unit Pelaksana Teknis (UPT)',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => $Es3, 'ref_jabatan_satker_id' => $getIdJabatan('upt'),
                'pola' => '[PARENT][INC:2, START:11]' // 090111, 090112...
            ],
            [
                'nama_rumus' => 'Wakil Dekan (PTKN)',
                'tingkat_wilayah_id' => $PTKN, 'jenis_satker_id' => $Es3, 'ref_jabatan_satker_id' => $getIdJabatan('wakil_dekan'),
                // Karena parent-nya adalah Dekan (5 digit: 21901), maka tinggal ditambah 1 digit sesuai aturan: 219011
                'pola' => '[PARENT][INC:1, START:1]' 
            ],

            // ==========================================
            // IV. TAMBAHAN GLOBAL / LAINNYA
            // ==========================================
            [
                'nama_rumus' => 'Kepala KUA (Kecamatan)',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => $getIdJabatan('kua'),
                'pola' => '[PARENT]9[INC:3, START:001]' // KUA biasanya 3 digit urut
            ],
            [
                'nama_rumus' => 'Kepala Madrasah Tsanawiyah (MTsN)',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => $getIdJabatan('mtsn'),
                'pola' => '[PARENT]9[INC:2, START:31]' 
            ],
            [
                'nama_rumus' => 'Kepala Madrasah Aliyah (MAN)',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => $getIdJabatan('man'),
                'pola' => '[PARENT]9[INC:2, START:61]' 
            ],
            [
                'nama_rumus' => 'Tata Usaha (Umum/Global)',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => $getIdJabatan('tu') ?? $getIdJabatan('tata_usaha'),
                'pola' => '[PARENT]01' // FIX CODE
            ],
            [
                'nama_rumus' => 'Default / Sapu Jagat (Berlaku Jika Rumus Lain Tidak Ada)',
                'tingkat_wilayah_id' => null, 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null,
                'pola' => '[PARENT][INC:2, START:01]' // Default urut 2 digit
            ],
        ];

        // 4. Eksekusi Insert
        $insertData = [];
        foreach ($daftarRumus as $r) {
            $insertData[] = [
                'nama_rumus' => $r['nama_rumus'],
                'tingkat_wilayah_id' => $r['tingkat_wilayah_id'],
                'jenis_satker_id' => $r['jenis_satker_id'],
                'ref_jabatan_satker_id' => $r['ref_jabatan_satker_id'],
                'pola' => $r['pola'],
                'is_applied' => true, // Semua rumus ini langsung diaktifkan!
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('rumus_kodes')->insert($insertData);

        $this->command->info('17 Rumus Kemenag berhasil di-Seed dan Diaktifkan!');
    }
}