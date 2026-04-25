<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Satker;
use App\Models\MJenisSatker;
use App\Models\RumusKode;
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
        // 1. Decode string JSON menjadi Array PHP sebelum divalidasi
        $customMap = null;
        if ($request->filled('custom_names_map')) {
            $customMap = json_decode($request->custom_names_map, true);
            // Jika hasilnya string kosong "{}", jadikan null
            if (empty($customMap)) {
                $customMap = null;
            }
        }

        // Merge input request agar validasi bisa membaca array tersebut
        $request->merge([
            'custom_names_map_array' => $customMap
        ]);

        $request->validate([
            'nama_rumus' => 'required|string|max:255|unique:rumus_kodes,nama_rumus',
            'pola' => 'required|string|max:255',
            'is_auto_name' => 'nullable|boolean',
            'base_auto_name' => 'nullable|string|max:255',
            'is_name_locked' => 'nullable|boolean',
            'custom_names_map_array' => 'nullable|array' // Validasi array yang sudah di-decode
        ]);

        RumusKode::create([
            'nama_rumus' => $request->nama_rumus,
            'pola' => $request->pola,
            // Baris 'keterangan' DIHAPUS DARI SINI
            'is_applied' => $request->has('is_applied'),
            'is_auto_name' => $request->has('is_auto_name'),
            'base_auto_name' => $request->base_auto_name,
            'is_name_locked' => $request->has('is_name_locked'),
            'custom_names_map' => $customMap, 
        ]);

        return redirect()->route('admin.setting-kode.index')->with('success', 'Rumus berhasil ditambahkan!');
    }

    public function updateRumus(Request $request, $id)
    {
        $rumus = RumusKode::findOrFail($id);

        // Decode JSON dari form Edit
        $customMap = null;
        if ($request->filled('custom_names_map')) {
            $customMap = json_decode($request->custom_names_map, true);
            if (empty($customMap)) {
                $customMap = null;
            }
        }
        
        $request->merge([
            'custom_names_map_array' => $customMap
        ]);

        $request->validate([
            'nama_rumus' => 'required|string|max:255|unique:rumus_kodes,nama_rumus,' . $id,
            'pola' => 'required|string|max:255',
            'is_auto_name' => 'nullable|boolean',
            'base_auto_name' => 'nullable|string|max:255',
            'is_name_locked' => 'nullable|boolean',
            'custom_names_map_array' => 'nullable|array'
        ]);

        $rumus->update([
            'nama_rumus' => $request->nama_rumus,
            'pola' => $request->pola,
            // Baris 'keterangan' DIHAPUS DARI SINI
            'is_applied' => $request->has('is_applied'),
            'is_auto_name' => $request->has('is_auto_name'),
            'base_auto_name' => $request->base_auto_name,
            'is_name_locked' => $request->has('is_name_locked'),
            'custom_names_map' => $customMap,
        ]);

        return redirect()->route('admin.setting-kode.index')->with('success', 'Rumus berhasil diperbarui!');
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
    // FITUR MANAJEMEN STATUS JABATAN & ANGKA TETAP
    // =========================================================================
    public function storeJabatan(Request $request)
    {
        $request->validate([
            'label_jabatan' => 'required|string', 
            'kode_dasar' => 'nullable|string', 
            'tingkat_wilayah_id' => 'nullable|integer'
        ]);

        $kodeDasar = $request->kode_dasar;
        if ($kodeDasar === 'null' || trim($kodeDasar) === '') {
            $kodeDasar = null;
        }

        DB::table('ref_jabatan_satker')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(), 
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

    // ==============================================================
    // FUNGSI UPDATE MANUAL KODE SATKER (BULK + CASCADE)
    // ==============================================================
    public function updateManualBulk(Request $request)
    {
        $request->validate([
            'kode_satker_baru' => 'required|array',
        ]);

        $changes = 0;

        DB::beginTransaction();
        try {
            foreach ($request->kode_satker_baru as $id => $kodeBaru) {
                $satker = Satker::find($id);
                $kodeBaru = trim($kodeBaru);
                
                // Pastikan kode baru tidak kosong dan berbeda dari yang lama
                if ($satker && !empty($kodeBaru) && $satker->kode_satker !== $kodeBaru) {
                    
                    // Cek kode unik (agar tidak ada satker kembar di periode yang sama)
                    $exists = DB::table('satker')
                        ->where('periode_id', $satker->periode_id)
                        ->where('kode_satker', $kodeBaru)
                        ->where('id', '!=', $id)
                        ->exists();

                    if ($exists) {
                        return response()->json([
                            'success' => false,
                            'message' => "Gagal: Kode '$kodeBaru' sudah digunakan oleh Satker lain pada periode ini!"
                        ], 422);
                    }

                    // Simpan kode lama untuk keperluan replace di anak-anaknya
                    $oldKode = $satker->kode_satker;
                    
                    // 1. Update kode Induk
                    $satker->update([
                        'kode_satker' => $kodeBaru
                    ]);
                    $changes++;

                    // 2. Cascade: Update otomatis semua kode anak keturunannya
                    $changes += $this->cascadeUpdateKode($satker->id, $oldKode, $kodeBaru);
                }
            }
            
            DB::commit();

            if ($changes > 0) {
                return response()->json([
                    'success' => true, 
                    'message' => "Berhasil menyimpan dan mencascade perubahan $changes kode satker."
                ]);
            } else {
                return response()->json([
                    'success' => true, 
                    'message' => "Tidak ada perubahan kode satker yang dilakukan."
                ]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => 'Gagal memperbarui kode: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fungsi Private Rekursif untuk Efek Domino (Cascade) Kode Anak
     */
    private function cascadeUpdateKode($parentId, $oldParentKode, $newParentKode)
    {
        $changes = 0;
        
        // Cari semua anak langsung dari Parent ini
        $children = Satker::where('parent_satker_id', $parentId)->get();
        
        foreach ($children as $child) {
            $childOldKode = $child->kode_satker;
            
            // Pastikan kode anak diawali dengan kode induk yang lama
            if (str_starts_with($childOldKode, $oldParentKode)) {
                
                // Buat kode baru: Awalan Induk Baru + Sisa Kode Anak
                $childNewKode = $newParentKode . substr($childOldKode, strlen($oldParentKode));
                
                // Update Anak
                $child->update([
                    'kode_satker' => $childNewKode
                ]);
                $changes++;
                
                // Teruskan efek domino ke cucu-cucunya (Rekursif)
                $changes += $this->cascadeUpdateKode($child->id, $childOldKode, $childNewKode);
            }
        }
        
        return $changes;
    }
}