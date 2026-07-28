<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ambil data jabatan sebagai referensi
        $jabatans = DB::table('ref_jabatan_satker')->get();
        if ($jabatans->isEmpty()) return;

        // 2. Cari ID "Tidak ada jabatan"
        $tidakAdaJabatanId = DB::table('ref_jabatan_satker')
            ->where('label_jabatan', 'ILIKE', '%Tidak ada%')
            ->orWhere('key_jabatan', 'tidak_ada')
            ->value('id');

        // 3. Cari HANYA data satker lama yang id jabatannya masih kosong (NULL)
        $satkers = DB::table('satker')->whereNull('ref_jabatan_satker_id')->get();

        foreach($satkers as $satker) {
            $matchedId = null;
            $nama = strtolower($satker->nama_satker);
            
            // Pencocokan cerdas berdasarkan nama satker
            foreach($jabatans as $j) {
                $label = strtolower($j->label_jabatan);
                if (str_contains($label, 'tidak ada')) continue;

                if (
                    str_contains($nama, $label) || 
                    (str_contains($label, 'dekan') && str_contains($nama, 'fakultas')) ||
                    (str_contains($label, 'tata usaha') && str_contains($nama, 'tata usaha'))
                ) {
                    $matchedId = $j->id;
                    break;
                }
            }
            
            // Jika tidak ada yang cocok, jadikan "Tidak Ada Jabatan"
            if (!$matchedId) {
                $matchedId = $tidakAdaJabatanId;
            }
            
            // Update data satker lama tersebut
            DB::table('satker')
                ->where('id', $satker->id)
                ->update(['ref_jabatan_satker_id' => $matchedId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Jika di-rollback, kembalikan ke NULL (opsional, untuk safety)
        // DB::table('satker')->update(['ref_jabatan_satker_id' => null]);
    }
};