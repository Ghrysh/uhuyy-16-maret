<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RumusJabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        // ===================================================================================
        // 0. PEMBERSIHAN RUMUS LAMA (Jalankan di awal agar tidak kena error relasi (Foreign Key)
        // saat kita menghapus duplikat jabatan di bawah)
        // ===================================================================================
        DB::table('rumus_kodes')->delete();


        // ===================================================================================
        // 1. DATA REF JABATAN SATKER (Total 40 Data)
        // Logika: Gunakan yang lama, Singkirkan duplikat baru, Tambahkan jika belum ada.
        // ===================================================================================
        $jabatans = [
            // Batch 1
            ['id' => '2ed964df-20ad-4d1f-9594-914aeb8a720a', 'tingkat_wilayah_id' => null, 'parent_id' => null, 'key_jabatan' => 'manajerial', 'label_jabatan' => 'Manajerial', 'kode_dasar' => null, 'is_increment' => false],
            ['id' => 'ba77a68c-08cc-41b7-aec2-3424c73370de', 'tingkat_wilayah_id' => null, 'parent_id' => null, 'key_jabatan' => 'jabatan_fungsional', 'label_jabatan' => 'Jabatan Fungsional', 'kode_dasar' => null, 'is_increment' => false],
            ['id' => 'a351b5e1-9f4e-424b-a528-64f266b4fed4', 'tingkat_wilayah_id' => null, 'parent_id' => null, 'key_jabatan' => 'pelaksana', 'label_jabatan' => 'Pelaksana', 'kode_dasar' => '7', 'is_increment' => false],
            ['id' => 'e864186e-c937-4112-9ea2-72abbb914cdc', 'tingkat_wilayah_id' => 4, 'parent_id' => null, 'key_jabatan' => 'wakil_jurusan', 'label_jabatan' => 'Wakil Jurusan', 'kode_dasar' => '901011', 'is_increment' => true],
            ['id' => '601384de-491e-40fc-bdc2-2e6b73ed0217', 'tingkat_wilayah_id' => 4, 'parent_id' => null, 'key_jabatan' => 'lembaga', 'label_jabatan' => 'Lembaga', 'kode_dasar' => '921', 'is_increment' => false],
            ['id' => 'd7fd2c1d-fcc8-47f4-9ff3-30bda684103a', 'tingkat_wilayah_id' => 4, 'parent_id' => null, 'key_jabatan' => 'sekretaris_lembaga', 'label_jabatan' => 'Sekretaris Lembaga', 'kode_dasar' => '9211', 'is_increment' => true],
            ['id' => '902e1eb3-7d09-4530-85aa-3e56b01f2339', 'tingkat_wilayah_id' => 4, 'parent_id' => null, 'key_jabatan' => 'pusat', 'label_jabatan' => 'Pusat', 'kode_dasar' => '92101', 'is_increment' => false],
            ['id' => '0495a138-02ed-40d0-8b64-3a1f9393a251', 'tingkat_wilayah_id' => 4, 'parent_id' => null, 'key_jabatan' => 'sekretaris_kepala_pusat', 'label_jabatan' => 'Sekretaris Kepala Pusat', 'kode_dasar' => '921011', 'is_increment' => true],
            ['id' => 'd4bdb59d-49de-4991-8a5a-86ebccc38093', 'tingkat_wilayah_id' => 2, 'parent_id' => null, 'key_jabatan' => 'jabatan_kanwil', 'label_jabatan' => 'Jabatan di Kanwil', 'kode_dasar' => null, 'is_increment' => false],
            ['id' => '23d927c3-b324-457d-a78f-19908484ffb8', 'tingkat_wilayah_id' => 2, 'parent_id' => null, 'key_jabatan' => 'jabatan_kotakab', 'label_jabatan' => 'Jabatan di Kota/Kab', 'kode_dasar' => '21', 'is_increment' => false],
            
            // Batch 2
            ['id' => 'f6d8e8dc-7599-4240-ab16-59c55fe97b6f', 'tingkat_wilayah_id' => null, 'parent_id' => 'd4bdb59d-49de-4991-8a5a-86ebccc38093', 'key_jabatan' => 'tu_kanwil', 'label_jabatan' => 'Tata Usaha', 'kode_dasar' => '01', 'is_increment' => false],
            ['id' => '9f802990-eeaf-47ab-82da-e622a65b0cae', 'tingkat_wilayah_id' => null, 'parent_id' => 'd4bdb59d-49de-4991-8a5a-86ebccc38093', 'key_jabatan' => 'non_tu_kanwil', 'label_jabatan' => 'Non Tata Usaha', 'kode_dasar' => '04', 'is_increment' => false],
            ['id' => 'e2e9cfe7-1b10-49e0-8e5a-c1be8a191f0c', 'tingkat_wilayah_id' => null, 'parent_id' => '23d927c3-b324-457d-a78f-19908484ffb8', 'key_jabatan' => 'tu_kab', 'label_jabatan' => 'Tata Usaha', 'kode_dasar' => '01', 'is_increment' => false],
            ['id' => '4c040675-15d3-48c7-b34a-3a240c922ddc', 'tingkat_wilayah_id' => null, 'parent_id' => '23d927c3-b324-457d-a78f-19908484ffb8', 'key_jabatan' => 'bimas_islam', 'label_jabatan' => 'Bimas Islam', 'kode_dasar' => '03', 'is_increment' => false],
            ['id' => 'b1f03142-ccac-40c0-948d-e3106c89f36d', 'tingkat_wilayah_id' => null, 'parent_id' => '23d927c3-b324-457d-a78f-19908484ffb8', 'key_jabatan' => 'madrasah', 'label_jabatan' => 'Madrasah', 'kode_dasar' => null, 'is_increment' => false],
            ['id' => '06b41252-688a-4493-961c-23c2c14fa6ed', 'tingkat_wilayah_id' => null, 'parent_id' => 'b1f03142-ccac-40c0-948d-e3106c89f36d', 'key_jabatan' => 'min', 'label_jabatan' => 'Madrasah Ibtidaiyah Negeri', 'kode_dasar' => '02', 'is_increment' => false],
            ['id' => '73d3c5c6-d34a-4c6e-bc86-29d2d9b24d3d', 'tingkat_wilayah_id' => null, 'parent_id' => 'b1f03142-ccac-40c0-948d-e3106c89f36d', 'key_jabatan' => 'mtsn', 'label_jabatan' => 'Madrasah Tsanawiyah Negeri', 'kode_dasar' => '02', 'is_increment' => false],
            ['id' => '883b7afb-6dc0-4153-a0ee-427dd3d07338', 'tingkat_wilayah_id' => null, 'parent_id' => 'b1f03142-ccac-40c0-948d-e3106c89f36d', 'key_jabatan' => 'man', 'label_jabatan' => 'Madrasah Aliyah Negeri', 'kode_dasar' => '02', 'is_increment' => false],
            ['id' => 'c0664b8f-6d73-484d-8709-27a50a1e8533', 'tingkat_wilayah_id' => 4, 'parent_id' => null, 'key_jabatan' => 'wakil_kepala_prodi', 'label_jabatan' => 'Seketaris Prodi', 'kode_dasar' => '90101011', 'is_increment' => true],
            ['id' => '855bbff8-e2cc-4644-8c8e-fab29b6f42f6', 'tingkat_wilayah_id' => 4, 'parent_id' => null, 'key_jabatan' => 'kepala_prodi', 'label_jabatan' => 'Ketua Prodi', 'kode_dasar' => '01', 'is_increment' => false],
            
            // Batch 3
            ['id' => '70f83a94-1e7e-49ff-afa3-53738b4c1014', 'tingkat_wilayah_id' => null, 'parent_id' => null, 'key_jabatan' => 'tidak_ada_jabatan', 'label_jabatan' => 'Tidak ada Jabatan', 'kode_dasar' => '00', 'is_increment' => false],
            ['id' => 'f9cf87f3-29eb-4258-b076-2a12a01e642e', 'tingkat_wilayah_id' => 4, 'parent_id' => null, 'key_jabatan' => 'wakil_direktur_pascasarjana_ptkn', 'label_jabatan' => 'Wakil Direktur Pascasarjana PTKN', 'kode_dasar' => null, 'is_increment' => false],
            ['id' => '2440ccd7-30b4-480b-aebf-8518c4379af4', 'tingkat_wilayah_id' => 4, 'parent_id' => null, 'key_jabatan' => 'wakil_rektor', 'label_jabatan' => 'Wakil Rektor', 'kode_dasar' => '9', 'is_increment' => true],
            ['id' => '818e6b6d-b589-4786-835e-953c8fd1ae0e', 'tingkat_wilayah_id' => 4, 'parent_id' => null, 'key_jabatan' => 'dekan', 'label_jabatan' => 'Dekan', 'kode_dasar' => '9', 'is_increment' => false],
            ['id' => '056f5f93-7def-4b7e-8b38-711aaec2022b', 'tingkat_wilayah_id' => 4, 'parent_id' => null, 'key_jabatan' => 'direktur_pascasarjana', 'label_jabatan' => 'Direktur Pascasarjana', 'kode_dasar' => '9', 'is_increment' => false],
            ['id' => '4a1811d3-aa8e-4fc6-94fa-063a5bb38c28', 'tingkat_wilayah_id' => 4, 'parent_id' => null, 'key_jabatan' => 'satuan_pengawas_internal_dan_lembaga_ptkn', 'label_jabatan' => 'Satuan Pengawas Internal dan Lembaga PTKN', 'kode_dasar' => '9', 'is_increment' => false],
            ['id' => 'd15eda72-db79-421f-8ba6-e65694f5470b', 'tingkat_wilayah_id' => 4, 'parent_id' => null, 'key_jabatan' => 'wakil_dekan', 'label_jabatan' => 'Wakil Dekan', 'kode_dasar' => '9', 'is_increment' => true],
            ['id' => '47b712fc-bd47-4564-acd1-e21287cec496', 'tingkat_wilayah_id' => 4, 'parent_id' => null, 'key_jabatan' => 'sekretaris_spi', 'label_jabatan' => 'Sekretaris SPI', 'kode_dasar' => null, 'is_increment' => false],
            ['id' => 'c17d4bba-0974-4717-9a6d-0f58c7450e64', 'tingkat_wilayah_id' => 4, 'parent_id' => null, 'key_jabatan' => 'ketua_jurusan_ptkn', 'label_jabatan' => 'Ketua Jurusan PTKN', 'kode_dasar' => null, 'is_increment' => false],
            ['id' => '11abc7e2-45c4-4b86-8199-343b9687fc97', 'tingkat_wilayah_id' => 4, 'parent_id' => null, 'key_jabatan' => 'kepala_pusat_ptkn', 'label_jabatan' => 'Kepala Pusat PTKN', 'kode_dasar' => null, 'is_increment' => false],
            
            // Batch 4
            ['id' => 'e6f66877-002d-497e-91b6-eccdb1180373', 'tingkat_wilayah_id' => 4, 'parent_id' => null, 'key_jabatan' => 'sekretaris_jurusan_ptkn', 'label_jabatan' => 'Sekretaris Jurusan PTKN', 'kode_dasar' => null, 'is_increment' => false],
            ['id' => 'a7073e70-d6b1-46e4-8429-ce9334f4eb56', 'tingkat_wilayah_id' => 4, 'parent_id' => null, 'key_jabatan' => 'tidak_ada_sekretaris_jurusan', 'label_jabatan' => 'Tidak ada Sekretaris Jurusan', 'kode_dasar' => '0', 'is_increment' => false],
            ['id' => '4e971613-662f-4582-8af8-ff130c68eb8e', 'tingkat_wilayah_id' => 4, 'parent_id' => null, 'key_jabatan' => 'sekretaris_jurusan_s_2s_3_ptkn', 'label_jabatan' => 'Sekretaris Jurusan S-2/S-3 PTKN', 'kode_dasar' => null, 'is_increment' => false],
            ['id' => '418b8309-806f-46d2-b32a-e2559989d55e', 'tingkat_wilayah_id' => 4, 'parent_id' => null, 'key_jabatan' => 'sekretaris_pusat_ptkn', 'label_jabatan' => 'Sekretaris Pusat PTKN', 'kode_dasar' => null, 'is_increment' => false],
            ['id' => '440ce913-6826-42d7-b449-f3a01215fed7', 'tingkat_wilayah_id' => 4, 'parent_id' => null, 'key_jabatan' => 'ketua_program_studi_prodi_s_1_ptkn', 'label_jabatan' => 'Ketua Program Studi (Prodi) S-1 PTKN', 'kode_dasar' => null, 'is_increment' => false],
            ['id' => '85173fe4-e164-40e6-8252-1a8c4a317412', 'tingkat_wilayah_id' => 4, 'parent_id' => null, 'key_jabatan' => 'ketua_program_studi_prodi_s_2s_3_ptkn', 'label_jabatan' => 'Ketua Program Studi (Prodi) S-2/S-3 PTKN', 'kode_dasar' => null, 'is_increment' => false],
            ['id' => 'ba023391-85cc-4634-81ea-799ae19c25e9', 'tingkat_wilayah_id' => 4, 'parent_id' => null, 'key_jabatan' => 'ketua_program_studi_prodi_ptkn', 'label_jabatan' => 'Ketua Program Studi (Prodi) PTKN', 'kode_dasar' => null, 'is_increment' => false],
            ['id' => '89a11448-84a8-431e-b7c9-25ef356739f2', 'tingkat_wilayah_id' => 4, 'parent_id' => null, 'key_jabatan' => 'sekretaris_program_studi_prodi_ptkn', 'label_jabatan' => 'Sekretaris Program Studi (Prodi) PTKN', 'kode_dasar' => null, 'is_increment' => false],
            ['id' => 'dd9b4167-5d32-4049-8baa-3c10c07d3074', 'tingkat_wilayah_id' => 4, 'parent_id' => null, 'key_jabatan' => 'sekretaris_program_studi_prodi_s_2s_3_ptkn', 'label_jabatan' => 'Sekretaris Program Studi (Prodi) S-2/S-3 PTKN', 'kode_dasar' => null, 'is_increment' => false],
            ['id' => '60e0fd36-dfc4-44cc-b4b3-5932dab85fac', 'tingkat_wilayah_id' => 4, 'parent_id' => null, 'key_jabatan' => 'sekretaris_laboratorium_ptkn', 'label_jabatan' => 'Sekretaris Laboratorium PTKN', 'kode_dasar' => null, 'is_increment' => false],
        ];

        // MAPPING 1: Petakan ID yang ada di file seeder (ID Baru) ke Labelnya
        $newIdToLabelMap = [];
        foreach ($jabatans as $jb) {
            $newIdToLabelMap[$jb['id']] = $jb['label_jabatan'];
        }

        // MAPPING 2: Mencari "Data Lama", Menghapus "Duplikat Baru", dan Menyimpan ID aslinya
        $realIdMap = [];
        foreach ($jabatans as $jb) {
            // Ambil semua data dengan label yang sama, urutkan dari yang paling tua (NULL/Lama duluan)
            $records = DB::table('ref_jabatan_satker')
                ->where('label_jabatan', $jb['label_jabatan'])
                ->orderByRaw('created_at ASC NULLS FIRST') 
                ->get();

            if ($records->count() > 0) {
                // 1. GUNAKAN YANG LAMA: Ambil ID dari data pertama (yang tertua)
                $realRecord = $records->first();
                $realIdMap[$jb['label_jabatan']] = $realRecord->id;

                // 2. SINGKIRKAN YANG BARU: Jika ada lebih dari 1 data, berarti ada duplikat yang baru telanjur masuk
                if ($records->count() > 1) {
                    $duplicateIds = $records->skip(1)->pluck('id')->toArray();
                    DB::table('ref_jabatan_satker')->whereIn('id', $duplicateIds)->delete();
                }
            } else {
                // 3. TAMBAHKAN JIKA BELUM ADA: Karena belum ada di database, kita pakai ID baru dari Seeder
                $realIdMap[$jb['label_jabatan']] = $jb['id'];
            }
        }

        // EKSEKUSI INSERT/UPDATE KE TABEL JABATAN MENGGUNAKAN ID LAMA (ATAU BARU JIKA KOSONG)
        foreach ($jabatans as $jb) {
            $realId = $realIdMap[$jb['label_jabatan']];

            // Cari ID sesungguhnya untuk parent_id agar relasi parent-child tidak terputus
            $realParentId = null;
            if ($jb['parent_id']) {
                $parentLabel = $newIdToLabelMap[$jb['parent_id']] ?? null;
                if ($parentLabel && isset($realIdMap[$parentLabel])) {
                    $realParentId = $realIdMap[$parentLabel];
                }
            }

            // Cek apakah data sudah ada (berdasarkan ID Asli)
            $exists = DB::table('ref_jabatan_satker')->where('id', $realId)->exists();

            if ($exists) {
                // Hanya update atributnya saja, ID tetap menggunakan ID lama
                DB::table('ref_jabatan_satker')->where('id', $realId)->update([
                    'tingkat_wilayah_id' => $jb['tingkat_wilayah_id'],
                    'parent_id' => $realParentId, 
                    'key_jabatan' => $jb['key_jabatan'],
                    'kode_dasar' => $jb['kode_dasar'],
                    'is_increment' => $jb['is_increment'],
                    'updated_at' => $now
                ]);
            } else {
                // Jika label belum ada, tambahkan sebagai data baru
                DB::table('ref_jabatan_satker')->insert([
                    'id' => $realId,
                    'tingkat_wilayah_id' => $jb['tingkat_wilayah_id'],
                    'parent_id' => $realParentId,
                    'key_jabatan' => $jb['key_jabatan'],
                    'label_jabatan' => $jb['label_jabatan'],
                    'kode_dasar' => $jb['kode_dasar'],
                    'is_increment' => $jb['is_increment'],
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
            }
        }

        // ===================================================================================
        // 2. DATA RUMUS KODES (Update 86 Data dari SQL Terbaru)
        // Insert ulang semua Rumus dengan memetakan ID jabatannya ke ID yang "Lama" tadi
        // ===================================================================================
        $rumusList = [
            // BATCH 1
            ['nama_rumus' => 'Biro di PTKN', 'jenis_satker_id' => 2, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:01]', 'is_applied' => false, 'tingkat_wilayah_id' => 4, 'is_auto_number' => true],
            ['nama_rumus' => 'Kepala Pusat PTKN sebagai Tugas Tambahan', 'jenis_satker_id' => 3, 'ref_jabatan_satker_id' => '11abc7e2-45c4-4b86-8199-343b9687fc97', 'pola' => '[PARENT][INC:2, START:01]', 'is_applied' => true, 'tingkat_wilayah_id' => 4, 'is_auto_number' => true],
            ['nama_rumus' => 'Ketua Jurusan S-2/S-3 PTKN sebagai Tugas Tambahan', 'jenis_satker_id' => 3, 'ref_jabatan_satker_id' => 'c17d4bba-0974-4717-9a6d-0f58c7450e64', 'pola' => '[PARENT][INC:2, START:51]', 'is_applied' => false, 'tingkat_wilayah_id' => 4, 'is_auto_number' => true],
            ['nama_rumus' => 'Eselon II Pusat', 'jenis_satker_id' => 2, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:01]', 'is_applied' => true, 'tingkat_wilayah_id' => 1, 'is_auto_number' => true],
            ['nama_rumus' => 'Perguruan Tinggi Keagamaan Negeri (PTKN)', 'jenis_satker_id' => 1, 'ref_jabatan_satker_id' => null, 'pola' => '[INC:2, START:21]', 'is_applied' => true, 'tingkat_wilayah_id' => 4, 'is_auto_number' => true],
            ['nama_rumus' => 'Wakil Rektor PTKN sebagai Tugas Tambahan', 'jenis_satker_id' => 2, 'ref_jabatan_satker_id' => '2440ccd7-30b4-480b-aebf-8518c4379af4', 'pola' => '[PARENT]9[INC:1, START:1]', 'is_applied' => true, 'tingkat_wilayah_id' => 4, 'is_auto_number' => true],
            ['nama_rumus' => 'Dekan PTKN sebagai Tugas Tambahan', 'jenis_satker_id' => 2, 'ref_jabatan_satker_id' => '818e6b6d-b589-4786-835e-953c8fd1ae0e', 'pola' => '[PARENT]9[INC:2, START:01]', 'is_applied' => true, 'tingkat_wilayah_id' => 4, 'is_auto_number' => true],
            ['nama_rumus' => 'Direktur Pascasarjana PTKN sebagai Tugas Tambahan', 'jenis_satker_id' => 2, 'ref_jabatan_satker_id' => '056f5f93-7def-4b7e-8b38-711aaec2022b', 'pola' => '[PARENT]9[INC:2, START:51]', 'is_applied' => true, 'tingkat_wilayah_id' => 4, 'is_auto_number' => true],
            ['nama_rumus' => 'SPI/Lembaga PTKN sebagai Tugas Tambahan', 'jenis_satker_id' => 2, 'ref_jabatan_satker_id' => '4a1811d3-aa8e-4fc6-94fa-063a5bb38c28', 'pola' => '[PARENT]9[INC:2, START:61]', 'is_applied' => true, 'tingkat_wilayah_id' => 4, 'is_auto_number' => true],
            ['nama_rumus' => 'Eselon III Pusat', 'jenis_satker_id' => 3, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:01]', 'is_applied' => true, 'tingkat_wilayah_id' => 1, 'is_auto_number' => true],

            // BATCH 2
            ['nama_rumus' => 'Kabag, Kabid, Pembimas, Kakanmenag pada Kanwil', 'jenis_satker_id' => 3, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:01]', 'is_applied' => true, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Kantor Kementerian Agama kabupaten/kota', 'jenis_satker_id' => 3, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:41]', 'is_applied' => true, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Wakil Dekan PTKN sebagai Tugas Tambahan', 'jenis_satker_id' => 3, 'ref_jabatan_satker_id' => 'd15eda72-db79-421f-8ba6-e65694f5470b', 'pola' => '[PARENT][INC:1, START:1]', 'is_applied' => true, 'tingkat_wilayah_id' => 4, 'is_auto_number' => true],
            ['nama_rumus' => 'Wakil Direktur Pascasarjana sebagai Tugas Tambahan', 'jenis_satker_id' => 3, 'ref_jabatan_satker_id' => 'f9cf87f3-29eb-4258-b076-2a12a01e642e', 'pola' => '[PARENT][INC:1, START:1]', 'is_applied' => true, 'tingkat_wilayah_id' => 4, 'is_auto_number' => true],
            ['nama_rumus' => 'Sekretaris SPI/Lembaga PTKN sebagai Tugas Tambahan', 'jenis_satker_id' => 3, 'ref_jabatan_satker_id' => '47b712fc-bd47-4564-acd1-e21287cec496', 'pola' => '[PARENT][INC:1, START:1]', 'is_applied' => true, 'tingkat_wilayah_id' => 4, 'is_auto_number' => true],
            ['nama_rumus' => 'Ketua Jurusan S-1 PTKN sebagai Tugas Tambahan', 'jenis_satker_id' => 3, 'ref_jabatan_satker_id' => 'c17d4bba-0974-4717-9a6d-0f58c7450e64', 'pola' => '[PARENT][INC:2, START:01]', 'is_applied' => true, 'tingkat_wilayah_id' => 4, 'is_auto_number' => true],
            ['nama_rumus' => 'BDK-BLA BMB PSDM', 'jenis_satker_id' => 3, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:11]', 'is_applied' => false, 'tingkat_wilayah_id' => 1, 'is_auto_number' => true],
            ['nama_rumus' => 'Eselon IV Pusat', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:01]', 'is_applied' => true, 'tingkat_wilayah_id' => 1, 'is_auto_number' => true],
            ['nama_rumus' => 'Kasubag, Kasi, Penyenggara pada Kanmenag', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:01]', 'is_applied' => true, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Sekretaris Jurusan S-1 PTKN sebagai Tugas Tambahan', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => 'e6f66877-002d-497e-91b6-eccdb1180373', 'pola' => '[PARENT][INC:1, START:1]', 'is_applied' => true, 'tingkat_wilayah_id' => 4, 'is_auto_number' => true],

            // BATCH 3
            ['nama_rumus' => 'Sekretaris Jurusan S-2/S-3 PTKN sebagai Tugas Tambahan', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => '4e971613-662f-4582-8af8-ff130c68eb8e', 'pola' => '[PARENT][INC:1, START:1]', 'is_applied' => true, 'tingkat_wilayah_id' => 4, 'is_auto_number' => true],
            ['nama_rumus' => 'Sekretaris Pusat PTKN sebagai Tugas Tambahan', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => '418b8309-806f-46d2-b32a-e2559989d55e', 'pola' => '[PARENT][INC:1, START:1]', 'is_applied' => true, 'tingkat_wilayah_id' => 4, 'is_auto_number' => true],
            ['nama_rumus' => 'Ketua Prodi S-1 PTKN sebagai Tugas Tambahan', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => '440ce913-6826-42d7-b449-f3a01215fed7', 'pola' => '[PARENT][INC:2, START:01]', 'is_applied' => true, 'tingkat_wilayah_id' => 4, 'is_auto_number' => true],
            ['nama_rumus' => 'Ketua Prodi S-2/S-3 PTKN sebagai Tugas Tambahan', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => '85173fe4-e164-40e6-8252-1a8c4a317412', 'pola' => '[PARENT][INC:2, START:51]', 'is_applied' => true, 'tingkat_wilayah_id' => 4, 'is_auto_number' => true],
            ['nama_rumus' => 'Ketua Laboratorium PTKN sebagai Tugas Tambahan', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => 'ba023391-85cc-4634-81ea-799ae19c25e9', 'pola' => '[PARENT][INC:2, START:81]', 'is_applied' => true, 'tingkat_wilayah_id' => 4, 'is_auto_number' => true],
            ['nama_rumus' => 'Sekretaris Prodi S-1 PTKN sebagai Tugas Tambahan', 'jenis_satker_id' => 5, 'ref_jabatan_satker_id' => '89a11448-84a8-431e-b7c9-25ef356739f2', 'pola' => '[PARENT][INC:1, START:1]', 'is_applied' => true, 'tingkat_wilayah_id' => 4, 'is_auto_number' => true],
            ['nama_rumus' => 'Sekretaris Prodi S-2/S-3 PTKN sebagai Tugas Tambahan', 'jenis_satker_id' => 5, 'ref_jabatan_satker_id' => '4e971613-662f-4582-8af8-ff130c68eb8e', 'pola' => '[PARENT][INC:1, START:1]', 'is_applied' => true, 'tingkat_wilayah_id' => 4, 'is_auto_number' => true],
            ['nama_rumus' => 'Sekretaris Laboratorium PTKN sebagai Tugas Tambahan', 'jenis_satker_id' => 5, 'ref_jabatan_satker_id' => '60e0fd36-dfc4-44cc-b4b3-5932dab85fac', 'pola' => '[PARENT][INC:1, START:1]', 'is_applied' => true, 'tingkat_wilayah_id' => 4, 'is_auto_number' => true],
            ['nama_rumus' => 'Kepala MTsN sebagai Tugas Tambahan', 'jenis_satker_id' => 5, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT]9[INC:2, START:31]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Kepala MIN sebagai Tugas Tambahan', 'jenis_satker_id' => 5, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT]9[INC:2, START:01]', 'is_applied' => true, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],

            // BATCH 4
            ['nama_rumus' => 'Kepala MAN IC sebagai Tugas Tambahan', 'jenis_satker_id' => 5, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT]9[INC:2, START:01]', 'is_applied' => false, 'tingkat_wilayah_id' => 1, 'is_auto_number' => true],
            ['nama_rumus' => 'Kepala MAN Kejuruan sebagai Tugas Tambahan', 'jenis_satker_id' => 5, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT]9[INC:2, START:31]', 'is_applied' => false, 'tingkat_wilayah_id' => 1, 'is_auto_number' => true],
            ['nama_rumus' => 'Kepala MAN Keagamaan sebagai Tugas Tambahan', 'jenis_satker_id' => 5, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT]9[INC:2, START:61]', 'is_applied' => false, 'tingkat_wilayah_id' => 1, 'is_auto_number' => true],
            ['nama_rumus' => 'Kepala KUA sebagai Tugas Tambahan', 'jenis_satker_id' => 5, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT]9[INC:2, START:01]', 'is_applied' => false, 'tingkat_wilayah_id' => 1, 'is_auto_number' => true],
            ['nama_rumus' => 'LDK BMB PSDM', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:01]', 'is_applied' => false, 'tingkat_wilayah_id' => 1, 'is_auto_number' => true],
            ['nama_rumus' => 'Eselon I Pusat', 'jenis_satker_id' => 1, 'ref_jabatan_satker_id' => null, 'pola' => '[INC:2, START:01]', 'is_applied' => true, 'tingkat_wilayah_id' => 1, 'is_auto_number' => true],
            ['nama_rumus' => 'Kanwil Kemenag Provinsi', 'jenis_satker_id' => 2, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:21]', 'is_applied' => false, 'tingkat_wilayah_id' => 1, 'is_auto_number' => true],
            ['nama_rumus' => 'Kepala MAN sebagai Tugas Tambahan', 'jenis_satker_id' => 5, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT]9[INC:2, START:61]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Wakil Kepala MIN sebagai Tugas Tambahan', 'jenis_satker_id' => 5, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:1, START:1]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Wakil Kepala MTsN sebagai Tugas Tambahan', 'jenis_satker_id' => 5, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:1, START:1]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],

            // BATCH 5
            ['nama_rumus' => 'Wakil Kepala MAN sebagai Tugas Tambahan', 'jenis_satker_id' => 5, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:1, START:1]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'KAUR TU Madrasah Eselon V (pada MIN/MTsN/MAN/MAN IC/MAN Kejuruan/Keagamaan)', 'jenis_satker_id' => 5, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:01]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Kepala Unit pada Madrasah (pada MIN/MTsN/MAN/MAN IC/MAN Kejuruan/Keagamaan)', 'jenis_satker_id' => 5, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:11]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Kepala Unit di SMPTKN, SMTKN, SMAKN (Kristen dan Katolik)', 'jenis_satker_id' => 5, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:11]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Kepala SMTKN (Kristen) sebagai Tugas Tambahan', 'jenis_satker_id' => 5, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT]9[INC:2, START:21]', 'is_applied' => false, 'tingkat_wilayah_id' => 1, 'is_auto_number' => true],
            ['nama_rumus' => 'Kepala SMAKN (Kristen) sebagai Tugas Tambahan', 'jenis_satker_id' => 5, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT]9[INC:2, START:41]', 'is_applied' => false, 'tingkat_wilayah_id' => 1, 'is_auto_number' => true],
            ['nama_rumus' => 'Kepala SMAKN (Katolik) sebagai Tugas Tambahan', 'jenis_satker_id' => 5, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT]9[INC:2, START:01]', 'is_applied' => false, 'tingkat_wilayah_id' => 1, 'is_auto_number' => true],
            ['nama_rumus' => 'Wakil Kepala MAN IC sebagai Tugas Tambahan', 'jenis_satker_id' => 5, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:1, START:1]', 'is_applied' => false, 'tingkat_wilayah_id' => 1, 'is_auto_number' => true],
            ['nama_rumus' => 'Wakil Kepala MAN Kejuruan sebagai Tugas Tambahan', 'jenis_satker_id' => 5, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:1, START:1]', 'is_applied' => false, 'tingkat_wilayah_id' => 1, 'is_auto_number' => true],
            ['nama_rumus' => 'Wakil Kepala MAN Keagamaan sebagai Tugas Tambahan', 'jenis_satker_id' => 5, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:1, START:1]', 'is_applied' => false, 'tingkat_wilayah_id' => 1, 'is_auto_number' => true],

            // BATCH 6
            ['nama_rumus' => 'Wakil Kepala SMPTKN (Kristen) sebagai Tugas Tambahan', 'jenis_satker_id' => 5, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:1, START:1]', 'is_applied' => false, 'tingkat_wilayah_id' => 1, 'is_auto_number' => true],
            ['nama_rumus' => 'Wakil Kepala SMTKN (Kristen) sebagai Tugas Tambahan', 'jenis_satker_id' => 5, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:1, START:1]', 'is_applied' => false, 'tingkat_wilayah_id' => 1, 'is_auto_number' => true],
            ['nama_rumus' => 'Wakil Kepala SMAKN (Kristen) sebagai Tugas Tambahan', 'jenis_satker_id' => 5, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:1, START:1]', 'is_applied' => false, 'tingkat_wilayah_id' => 1, 'is_auto_number' => true],
            ['nama_rumus' => 'Wakil Kepala SMAKN (Katolik) sebagai Tugas Tambahan', 'jenis_satker_id' => 5, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:1, START:1]', 'is_applied' => false, 'tingkat_wilayah_id' => 1, 'is_auto_number' => true],
            ['nama_rumus' => 'Eselon V Pusat', 'jenis_satker_id' => 5, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:01]', 'is_applied' => true, 'tingkat_wilayah_id' => 1, 'is_auto_number' => true],
            ['nama_rumus' => 'Kepala SMPTKN (Kristen) sebagai Tugas Tambahan', 'jenis_satker_id' => 5, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT]9[INC:2, START:01]', 'is_applied' => false, 'tingkat_wilayah_id' => 1, 'is_auto_number' => true],
            ['nama_rumus' => 'Bagian Tata Usaha', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:01]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Bidang Pendidikan Madrasah', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:02]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Bidang Pendidikan Diniyah dan Pondok Pesantren', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:03]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Bidang Pendidikan Agama Islam', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:04]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],

            // BATCH 7
            ['nama_rumus' => 'Bidang Pendidikan Agama dan Pendidikan Keagamaan Islam', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:05]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Bidang Urusan Agama Islam', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:07]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Bidang Penerangan Agama Islam, dan Pemberdayaan Zakat dan Wakaf', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:08]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Bidang Penyelenggaraan Haji dan Umrah', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:12]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Pembimbing Masyarakat Kristen', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:25]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Pembimbing Masyarakat Katolik', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:29]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Pembimbing Masyarakat Buddha', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:34]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Bidang Pendidikan Islam', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:06]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Bidang Bimbingan Masyarakat Islam', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:09]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Bidang Haji dan Bimbingan Masyarakat Islam', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:10]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],

            // BATCH 8
            ['nama_rumus' => 'Pembimbing Zakat dan Wakaf', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:11]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Bidang Pendidikan Agama Kristen', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:21]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Bidang Pendidikan Kristen', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:22]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Bidang Urusan Agama Kristen', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:23]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Bidang Bimbingan Masyarakat Kristen', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:24]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Bidang Pendidikan Katolik', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:26]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Bidang Urusan Agama Katolik', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:27]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Bidang Bimbingan Masyarakat Katolik', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:28]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Bidang Pendidikan Hindu', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:30]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Bidang Urusan Agama Hindu', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:31]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],

            // BATCH 9
            ['nama_rumus' => 'Bidang Bimbingan Masyarakat Hindu', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:32]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Pembimbing Masyarakat Hindu', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:33]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Pembimbing Masyarakat Khonghucu', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:35]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Kantor Kementerian Agama Kota Banda Aceh', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:41]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Kantor Kementerian Agama Kota Langsa', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:42]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Kantor Kementerian Agama Kota Lhokseumawe', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:43]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Kantor Kementerian Agama Kota Sabang', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:44]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Kantor Kementerian Agama Kota Subulussalam', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:45]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Kantor Kementerian Agama Kabupaten Aceh Barat', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:46]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Kantor Kementerian Agama Kabupaten Aceh Barat Daya', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:47]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],

            // BATCH 10
            ['nama_rumus' => 'Kantor Kementerian Agama Kabupaten Aceh Besar', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:48]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Kantor Kementerian Agama Kabupaten Aceh Jaya', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:49]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Kantor Kementerian Agama Kabupaten Aceh Selatan', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:50]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Kantor Kementerian Agama Kabupaten Aceh Singkil', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:51]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Kantor Kementerian Agama Kabupaten Aceh Tamiang', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:52]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Kantor Kementerian Agama Kabupaten Aceh Tengah', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:53]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Kantor Kementerian Agama Kabupaten Aceh Tenggara', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:54]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Kantor Kementerian Agama Kabupaten Aceh Timur', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:55]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Kantor Kementerian Agama Kabupaten Aceh Utara', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:56]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Kantor Kementerian Agama Kabupaten Bener Meriah', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:57]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],

            // BATCH 11
            ['nama_rumus' => 'Kantor Kementerian Agama Kabupaten Bireuen', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:58]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Kantor Kementerian Agama Kabupaten Gayo Lues', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:59]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Kantor Kementerian Agama Kabupaten Nagan Raya', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:60]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Kantor Kementerian Agama Kabupaten Pidie', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:61]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Kantor Kementerian Agama Kabupaten Pidie Jaya', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:62]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Kantor Kementerian Agama Kabupaten Simeulue', 'jenis_satker_id' => null, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:63]', 'is_applied' => false, 'tingkat_wilayah_id' => 2, 'is_auto_number' => true],
            ['nama_rumus' => 'Seksi Pendidikan Madrasah', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:02]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Seksi Pendidikan Diniyah dan Pondok Pesantren', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:03]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Seksi Pendidikan Agama dan Pendidikan Keagamaan Islam', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:04]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Seksi Pendidikan Agama Islam', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:05]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],

            // BATCH 12
            ['nama_rumus' => 'Seksi Pendidikan Islam', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:06]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Seksi Pendidikan dan Bimbingan Masyarakat Islam', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:07]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Seksi Pendidikan, Haji, dan Bimbingan Masyarakat Islam', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:08]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Penyelenggara Pendidikan, Haji, dan Bimbingan Masyarakat Islam', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:09]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Penyelenggara Pendidikan Islam', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:10]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Seksi Urusan Agama Islam dan Bina Syariah', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:11]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Seksi Penerangan Agama Islam', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:12]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Seksi Penerangan Agama Islam dan Pemberdayaan Zakat dan Wakaf', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:13]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Seksi Bimbingan Masyarakat Islam', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][PARENT][INC:2, START:14]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Seksi Haji dan Bimbingan Masyarakat Islam', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:15]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],

            // BATCH 13
            ['nama_rumus' => 'Penyelenggara Zakat dan Wakaf', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:16]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Penyelenggara Bimbingan Masyarakat Islam', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:17]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Penyelenggara Haji dan Bimbingan Masyarakat Islam', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:18]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Seksi Penyelenggaraan Haji dan Umrah', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:19]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Penyelenggara Haji dan Umrah', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:20]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Seksi Pendidikan Kristen', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:21]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Seksi Urusan Agama Kristen', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:22]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Seksi Bimbingan Masyarakat Kristen', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:23]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Seksi Urusan dan Pendidikan Kristen', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:24]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Penyelenggara Pendidikan Kristen', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:25]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],

            // BATCH 14
            ['nama_rumus' => 'Penyelenggara Urusan Agama Kristen', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:26]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Penyelenggara Kristen', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:27]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Seksi Pendidikan Katolik', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:28]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Seksi Urusan Agama Katolik', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:29]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Seksi Bimbingan Masyarakat Katolik', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:30]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Penyelenggara Pendidikan Katolik', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:31]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Penyelenggara Katolik', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:32]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Seksi Pendidikan Hindu', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:33]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Seksi Urusan Agama Hindu', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:34]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Seksi Bimbingan Masyarakat Hindu', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:35]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],

            // BATCH 15
            ['nama_rumus' => 'Penyelenggara Hindu', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:36]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Penyelenggara Hindu dan Buddha', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:37]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Penyelenggara Pendidikan Buddha', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:38]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Seksi Bimbingan Masyarakat Buddha', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:39]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Penyelenggara Buddha', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:40]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true],
            ['nama_rumus' => 'Penyelenggara Khonghucu', 'jenis_satker_id' => 4, 'ref_jabatan_satker_id' => null, 'pola' => '[PARENT][INC:2, START:41]', 'is_applied' => false, 'tingkat_wilayah_id' => 3, 'is_auto_number' => true]
        ];

        // EKSEKUSI INSERT RUMUS (Menggunakan ID Jabatan Asli yang dipetakan)
        foreach ($rumusList as $rm) {
            $realRefJabatanId = null;

            // Jika ada ref_jabatan_satker_id (ID Baru dari file Seeder)
            if ($rm['ref_jabatan_satker_id']) {
                // Temukan label jabatannya apa
                $label = $newIdToLabelMap[$rm['ref_jabatan_satker_id']] ?? null;
                // Jika label ketemu, cari ID asli ("Lama") dari database kita tadi
                if ($label && isset($realIdMap[$label])) {
                    $realRefJabatanId = $realIdMap[$label];
                }
            }

            DB::table('rumus_kodes')->insert([
                'nama_rumus' => $rm['nama_rumus'],
                'jenis_satker_id' => $rm['jenis_satker_id'],
                'ref_jabatan_satker_id' => $realRefJabatanId, // Sudah menggunakan ID lama/asli
                'pola' => $rm['pola'],
                'deskripsi' => $rm['deskripsi'] ?? null,
                'is_applied' => $rm['is_applied'],
                'tingkat_wilayah_id' => $rm['tingkat_wilayah_id'],
                'kode_awalan' => $rm['kode_awalan'] ?? null,
                'is_auto_number' => $rm['is_auto_number'],
                'digit_auto_number' => $rm['digit_auto_number'] ?? null,
                'default_nama_satker' => $rm['default_nama_satker'] ?? null,
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }
    }
}