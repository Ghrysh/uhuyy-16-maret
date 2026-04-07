<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Satker;
use App\Models\MJenisSatker;
use App\Models\RefJabatanSatker;
use App\Models\Wilayah;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SettingKodeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(function ($request, $next) {
                if (!auth()->user()->roles()->where('key', 'super_admin')->exists()) {
                    abort(403, 'Akses Ditolak. Halaman ini hanya untuk Super Admin.');
                }
                return $next($request);
            }),
        ];
    }

    public function index(Request $request)
    {
        $rumusList = DB::table('rumus_kodes')->orderBy('created_at', 'asc')->get();
        $jenisSatkers = MJenisSatker::all();
        $refJabatans = RefJabatanSatker::orderBy('label_jabatan')->get();
        
        // MENGAMBIL DATA LANGSUNG DARI TABEL WILAYAH
        $tingkatWilayahs = DB::table('m_tingkat_wilayah')->orderBy('id', 'asc')->get();
        $periodes = \App\Models\Periode::orderBy('created_at', 'asc')->get(); 

        $satkerRoots = Satker::with(['children', 'eselon'])
            ->whereNull('parent_satker_id')
            ->orderBy('kode_satker', 'asc')
            ->get();

        return view('admin.setting-kode.index', compact('rumusList', 'jenisSatkers', 'refJabatans', 'tingkatWilayahs', 'satkerRoots', 'periodes'));
    }

    public function storeRumus(Request $request)
    {
        $request->validate([
            'nama_rumus' => 'required|string',
            'pola' => 'required|string',
            'jenis_satker_id' => 'nullable',
            // Pastikan ini tingkat_wilayah_id, BUKAN wilayah_id
            'tingkat_wilayah_id' => 'nullable', 
            'ref_jabatan_satker_id' => 'nullable',
        ]);

        DB::table('rumus_kodes')->insert([
            'nama_rumus' => $request->nama_rumus,
            'pola' => $request->pola,
            
            // Konversi nilai 'all' dari form menjadi null (Global)
            'jenis_satker_id' => ($request->jenis_satker_id === 'all' || empty($request->jenis_satker_id)) ? null : $request->jenis_satker_id,
            
            // AMBIL DARI tingkat_wilayah_id
            'tingkat_wilayah_id' => ($request->tingkat_wilayah_id === 'all' || empty($request->tingkat_wilayah_id)) ? null : $request->tingkat_wilayah_id,
            
            'ref_jabatan_satker_id' => empty($request->ref_jabatan_satker_id) ? null : $request->ref_jabatan_satker_id,
            
            'is_applied' => 0, // Default tidak langsung aktif
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Rumus baru berhasil disimpan!');
    }

    public function updateRumus(Request $request, $id)
    {
        $request->validate([
            'nama_rumus' => 'required|string',
            'pola' => 'required|string',
        ]);

        DB::table('rumus_kodes')->where('id', $id)->update([
            'nama_rumus' => $request->nama_rumus,
            'pola' => $request->pola,
            // Tangkap filter baru
            'jenis_satker_id' => ($request->jenis_satker_id === 'all' || empty($request->jenis_satker_id)) ? null : $request->jenis_satker_id,
            'tingkat_wilayah_id' => ($request->tingkat_wilayah_id === 'all' || empty($request->tingkat_wilayah_id)) ? null : $request->tingkat_wilayah_id,
            'ref_jabatan_satker_id' => empty($request->ref_jabatan_satker_id) ? null : $request->ref_jabatan_satker_id,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Rumus & Filter berhasil diperbarui!');
    }

    public function destroyRumus($id)
    {
        DB::table('rumus_kodes')->where('id', $id)->delete();
        return back()->with('success', 'Rumus berhasil dihapus!');
    }

    public function applyRumus($id)
    {
        $rumus = DB::table('rumus_kodes')->where('id', $id)->first();
        if(!$rumus) return back()->with('error', 'Rumus tidak ditemukan!');

        DB::table('rumus_kodes')
            ->where(function($q) use ($rumus) {
                if ($rumus->tingkat_wilayah_id) $q->where('tingkat_wilayah_id', $rumus->tingkat_wilayah_id);
                else $q->whereNull('tingkat_wilayah_id');
            })
            ->where(function($q) use ($rumus) {
                if ($rumus->jenis_satker_id) $q->where('jenis_satker_id', $rumus->jenis_satker_id);
                else $q->whereNull('jenis_satker_id');
            })
            ->where(function($q) use ($rumus) {
                if ($rumus->ref_jabatan_satker_id) $q->where('ref_jabatan_satker_id', $rumus->ref_jabatan_satker_id);
                else $q->whereNull('ref_jabatan_satker_id');
            })
            ->update(['is_applied' => false]);

        DB::table('rumus_kodes')->where('id', $id)->update(['is_applied' => true]);

        return back()->with('success', "Rumus berhasil diaktifkan! Sistem akan menggunakan pola ini untuk kode Satker baru.");
    }

    // =========================================================================
    // FITUR EDIT KODE MANUAL (MASSAL) & CASCADE
    // =========================================================================
    public function updateKodeManual(Request $request)
    {
        DB::beginTransaction();
        try {
            if (!$request->has('satker')) {
                return response()->json(['success' => false, 'message' => 'Tidak ada data yang dikirim.'], 400);
            }

            foreach ($request->satker as $id => $kodeBaru) {
                $satker = DB::table('satker')->where('id', $id)->first();
                if (!$satker) continue;

                $kodeLama = $satker->kode_satker;
                
                if ($kodeLama !== $kodeBaru) {
                    // Validasi unik di periode yang sama
                    $exists = DB::table('satker')
                        ->where('periode_id', $satker->periode_id)
                        ->where('kode_satker', $kodeBaru)
                        ->where('id', '!=', $id)
                        ->exists();

                    if ($exists) {
                        return response()->json([
                            'success' => false,
                            'message' => "Gagal: Kode '$kodeBaru' sudah digunakan oleh Satker lain pada periode yang sama!"
                        ], 422);
                    }

                    // Eksekusi Update & Cascade
                    DB::table('satker')->where('id', $id)->update(['kode_satker' => $kodeBaru]);
                    $this->cascadeUpdateKode($id, $kodeLama, $kodeBaru);
                }
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Semua perubahan kode berhasil disimpan massal!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    private function cascadeUpdateKode($parentId, $oldParentCode, $newParentCode)
    {
        $children = DB::table('satker')->where('parent_satker_id', $parentId)->get();
        foreach ($children as $child) {
            $oldChildCode = $child->kode_satker;
            // Replace prefix lama dengan prefix baru
            if (str_starts_with($oldChildCode, $oldParentCode)) {
                $newChildCode = $newParentCode . substr($oldChildCode, strlen($oldParentCode));
                DB::table('satker')->where('id', $child->id)->update(['kode_satker' => $newChildCode]);
                // Rekursif ke anak berikutnya
                $this->cascadeUpdateKode($child->id, $oldChildCode, $newChildCode);
            }
        }
    }

    // =========================================================================
    // FITUR MANAJEMEN STATUS JABATAN & ANGKA TETAP
    // =========================================================================
    public function storeJabatan(Request $request)
    {
        $request->validate([
            'label_jabatan' => 'required|string', 
            'kode_dasar' => 'nullable|string', 
            'tingkat_wilayah_id' => 'nullable|integer'
        ]);

        // Bersihkan string "null" atau string kosong dari JavaScript agar masuk sebagai NULL betulan
        $kodeDasar = $request->kode_dasar;
        if ($kodeDasar === 'null' || trim($kodeDasar) === '') {
            $kodeDasar = null;
        }

        DB::table('ref_jabatan_satker')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(), // <-- GENERATE UUID MANUAL DI SINI
            'label_jabatan' => $request->label_jabatan,
            'key_jabatan' => \Illuminate\Support\Str::slug($request->label_jabatan, '_'),
            'kode_dasar' => $kodeDasar,
            'tingkat_wilayah_id' => $request->tingkat_wilayah_id,
            'created_at' => now(), 
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Status Jabatan berhasil ditambahkan!');
    }

    public function updateJabatan(Request $request, $id)
    {
        $request->validate([
            'label_jabatan' => 'required|string', 
            'kode_dasar' => 'nullable|string', 
            'tingkat_wilayah_id' => 'nullable|integer'
        ]);

        // Bersihkan string "null" atau string kosong dari JavaScript
        $kodeDasar = $request->kode_dasar;
        if ($kodeDasar === 'null' || trim($kodeDasar) === '') {
            $kodeDasar = null;
        }

        DB::table('ref_jabatan_satker')->where('id', $id)->update([
            'label_jabatan' => $request->label_jabatan,
            'key_jabatan' => \Illuminate\Support\Str::slug($request->label_jabatan, '_'),
            'kode_dasar' => $kodeDasar,
            'tingkat_wilayah_id' => $request->tingkat_wilayah_id,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Status Jabatan berhasil diperbarui!');
    }

    public function destroyJabatan($id)
    {
        $used = DB::table('rumus_kodes')->where('ref_jabatan_satker_id', $id)->exists();
        if ($used) return back()->with('error', 'Gagal: Status Jabatan ini sedang digunakan oleh Rumus!');
        
        DB::table('ref_jabatan_satker')->where('id', $id)->delete();
        return back()->with('success', 'Status Jabatan berhasil dihapus!');
    }
}