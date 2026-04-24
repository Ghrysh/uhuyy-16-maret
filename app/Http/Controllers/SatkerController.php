<?php

namespace App\Http\Controllers;

use App\Models\Satker;
use App\Models\Wilayah;
use App\Models\User;
use App\Models\Jabatan;
use App\Models\Periode;
use App\Models\MJenisPenugasan;
use App\Models\RefJabatanSatker;
use App\Models\MRole;
use App\Models\LogSistem;
use App\Models\MJenisSatker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class SatkerController extends Controller
{
    private function getPermissions()
    {
        $user = Auth::user();
        $userRoles = $user->roles;
        $isSuperAdmin = $userRoles->contains('key', 'super_admin');

        if ($isSuperAdmin) {
            return [
                'is_super'   => true, 'can_view' => true, 'all_access' => true,
                'visibility' => 'all', 'actions' => ['create', 'edit', 'delete', 'assign', 'end_self', 'end_other', 'cuti_self', 'cuti_other'],
                'allowed_ids' => []
            ];
        }

        $permissions = [
            'is_super' => false, 'can_view' => false, 'all_access' => false, 'view_only' => false,
            'visibility' => 'none', 'actions' => [], 'allowed_ids' => []
        ];

        foreach ($userRoles as $role) {
            $config = [];
            if ($role->key === 'pejabat') {
                $activeAssignment = \App\Models\Penugasan::where('user_id', $user->id)
                    ->where('status_aktif', 1)->with('jenisPenugasan')->first();
                if ($activeAssignment && $activeAssignment->jenisPenugasan) {
                    $config = $activeAssignment->jenisPenugasan->menus['satker'] ?? [];
                }
            } else {
                $config = $role->menus['satker'] ?? [];
            }

            if (!empty($config) && ($config['enabled'] ?? false)) {
                $permissions['can_view'] = true;
                if ($config['all_access'] ?? false) $permissions['all_access'] = true;
                if ($config['view_only'] ?? false) $permissions['view_only'] = true;

                // Ambil level visibilitas tertinggi
                $v = $config['visibility'] ?? 'none';
                $order = ['all' => 4, 'self_up_down' => 3, 'self_down' => 2, 'self_only' => 1, 'none' => 0];
                if ($order[$v] > $order[$permissions['visibility']]) {
                    $permissions['visibility'] = $v;
                }

                if (isset($config['actions'])) {
                    $permissions['actions'] = array_unique(array_merge($permissions['actions'], $config['actions']));
                }
            }
        }

        // =================================================================================
        // PERBAIKAN: CARI SATKER BERDASARKAN PENUGASAN AKTIF PEGAWAI DI TABEL PENUGASAN
        // =================================================================================
        $activeSatkerIds = \App\Models\Penugasan::where('user_id', $user->id)
            ->where('status_aktif', 1)
            ->pluck('satker_id')
            ->unique()
            ->toArray();
            
        // Fallback (jika tidak ada penugasan tapi di tabel user tercatat punya satker)
        if (empty($activeSatkerIds) && $user->satker_id) {
            $activeSatkerIds = [$user->satker_id];
        }

        if ($permissions['visibility'] !== 'all' && !empty($activeSatkerIds)) {
            $ids = $activeSatkerIds; // Masukkan semua satker di mana dia bekerja

            if ($permissions['visibility'] === 'self_up_down') {
                foreach ($activeSatkerIds as $sid) {
                    // Ambil Induk (Atasan)
                    $curr = Satker::find($sid);
                    while ($curr && $curr->parent_satker_id) {
                        $ids[] = $curr->parent_satker_id;
                        $curr = Satker::find($curr->parent_satker_id);
                    }
                    // Ambil Bawahan
                    $this->extractChildIds(Satker::with('childrenRecursive')->find($sid)->childrenRecursive, $ids);
                }
            } elseif ($permissions['visibility'] === 'self_down') {
                foreach ($activeSatkerIds as $sid) {
                    // Ambil Bawahan
                    $this->extractChildIds(Satker::with('childrenRecursive')->find($sid)->childrenRecursive, $ids);
                }
            }

            // Bersihkan duplikat array ID
            $permissions['allowed_ids'] = array_values(array_unique($ids));
        }
        // =================================================================================

        return $permissions;
    }

    public function index(Request $request)
    {
        $perm = $this->getPermissions();
        if (!$perm['can_view']) abort(403, 'Akses ditolak. Anda tidak memiliki izin melihat Satuan Kerja.');

        $user = Auth::user();
        $userRoles = $user->roles()->pluck('key')->toArray();

        $periodes = Periode::orderBy('created_at', 'asc')->get();
        // Pastikan activePeriodeId terdefinisi untuk filter
        $activePeriodeId = $request->input('periode_id', $periodes->first()->id ?? null);

        $satkerQuery = Satker::with('children')->where('periode_id', $activePeriodeId);
        $flatQuery = Satker::with(['childrenRecursive', 'wilayah', 'eselon'])->where('periode_id', $activePeriodeId);
        $tableQuery = Satker::query()->with(['wilayah', 'eselon'])->where('periode_id', $activePeriodeId);
        $listQuery = Satker::select('id', 'nama_satker', 'kode_satker', 'jenis_satker_id', 'periode_id')->where('periode_id', $activePeriodeId);
        $parentsQuery = Satker::query()->where('periode_id', $activePeriodeId);

        // PENERAPAN VISIBILITAS DARI REGULASI (Ini yang mengembalikan data Anda yang "hilang")
        if ($perm['visibility'] !== 'all') {
            $allowedIds = $perm['allowed_ids'];

            $satkerQuery->whereIn('id', $allowedIds);
            $flatQuery->whereIn('id', $allowedIds);
            $tableQuery->whereIn('id', $allowedIds);
            $listQuery->whereIn('id', $allowedIds);
            $parentsQuery->whereIn('id', $allowedIds);
        } else {
            $satkerQuery->whereNull('parent_satker_id');
            $flatQuery->whereNull('parent_satker_id');
        }

        $satkers = $satkerQuery->orderByRaw('LENGTH(kode_satker) ASC')->orderBy('kode_satker', 'asc')->get();
        $allSatkersFlat = $flatQuery->orderByRaw('LENGTH(kode_satker) ASC')->orderBy('kode_satker', 'asc')->get();

        $search = $request->query('search');
        $allSatkers = $tableQuery->when($search, function ($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('nama_satker', 'like', "%{$search}%")
                      ->orWhere('kode_satker', 'like', "%{$search}%");
                });
            })
            ->orderBy('kode_satker', 'asc')
            ->paginate(10);
        
        $listAllSatkers = $listQuery->orderBy('kode_satker', 'asc')->get();
        $parents = $parentsQuery->get();

// Master Data lainnya
        $jenisSatkers = DB::table('m_jenis_satker')->get();
        
        // ----------------------------------------------------------------------
        // JAWABAN: LOGIKA JABATAN FUNGSIONAL BERSARANG (Ekstrak 3 Digit Otomatis)
        // ----------------------------------------------------------------------
        $allJabatan = Jabatan::where('periode_id', $activePeriodeId)->with('fungsional')->get();
        
        $jabatanCategories = collect();
        $jabatanItems = collect();

        foreach ($allJabatan as $j) {
            $kode = trim($j->kode_jabatan);
            
            // Pastikan kodenya minimal 3 digit
            if (strlen($kode) >= 3) {
                // Ekstrak 3 digit pertama
                $prefix = substr($kode, 0, 3); 
                
                // Masukkan ke kategori (Hanya unik, jika belum ada maka tambahkan)
                if (!$jabatanCategories->has($prefix)) {
                    // Bersihkan nama dari embel-embel jenjang agar nama kategori lebih rapi
                    // (Menghapus kata Ahli Pertama, Ahli Muda, Pemula, Terampil, dll)
                    $baseName = preg_replace('/\s+(Ahli Pertama|Ahli Muda|Ahli Madya|Ahli Utama|Pemula|Terampil|Mahir|Penyelia)$/i', '', $j->nama_jabatan);
                    
                    $jabatanCategories->put($prefix, (object)[
                        'kode_jabatan' => $prefix,
                        'nama_jabatan' => trim($baseName)
                    ]);
                }

                // Masukkan data utuh ke dalam list jenjang
                $jabatanItems->push($j);
            }
        }

        // Urutkan berdasarkan kode
        $jabatanCategories = $jabatanCategories->sortBy('kode_jabatan')->values();
        $jabatanItems = $jabatanItems->sortBy('kode_jabatan')->values();
        
        // ----------------------------------------------------------------------
        // PENGHAPUSAN QUERY BERAT ($pegawais = User::all()) YANG MEMBUAT LEMOT!
        // ----------------------------------------------------------------------
        $pegawais = collect([]); 
        
        $jenis_penugasans = MJenisPenugasan::all();
        $wilayahs = Wilayah::whereIn('tingkat_wilayah_id', [1, 2, 4])->orderBy('kode_wilayah', 'asc')->get();
        $kabupaten = Wilayah::where('tingkat_wilayah_id', 3)->orderBy('kode_wilayah', 'asc')->get();
        $refJabatanSatker = RefJabatanSatker::orderBy('label_jabatan', 'asc')->get();
        $roles = MRole::whereIn('key', [
            'admin_satker', 
            'pejabat', 
            'admin_jafung_pengguna', 
            'admin_jafung_pembina'
        ])->get();
        
        $rumusList = DB::table('rumus_kodes')->orderBy('id', 'asc')->get();

        return view('admin.satker.index', compact(
            'satkers', 'allSatkers', 'listAllSatkers', 'wilayahs', 'kabupaten', 'parents', 
            'jenisSatkers', 'allJabatan', 'pegawais', 'jenis_penugasans', 'periodes', 'roles', 
            'userRoles', 'allSatkersFlat', 'refJabatanSatker', 'perm', 'rumusList', 'activePeriodeId',
            'jabatanCategories', 'jabatanItems' 
        ));
        
        // ----------------------------------------------------------------------
        // PENGHAPUSAN QUERY BERAT ($pegawais = User::all()) YANG MEMBUAT LEMOT!
        // ----------------------------------------------------------------------
        $pegawais = collect([]); 
        
        $jenis_penugasans = MJenisPenugasan::all();
        $wilayahs = Wilayah::whereIn('tingkat_wilayah_id', [1, 2, 4])->orderBy('kode_wilayah', 'asc')->get();
        $kabupaten = Wilayah::where('tingkat_wilayah_id', 3)->orderBy('kode_wilayah', 'asc')->get();
        $refJabatanSatker = RefJabatanSatker::orderBy('label_jabatan', 'asc')->get();
        $roles = MRole::whereIn('key', [
            'admin_satker', 
            'pejabat', 
            'admin_jafung_pengguna', 
            'admin_jafung_pembina'
        ])->get();
        
        $rumusList = DB::table('rumus_kodes')->orderBy('id', 'asc')->get();

        return view('admin.satker.index', compact(
            'satkers', 'allSatkers', 'listAllSatkers', 'wilayahs', 'kabupaten', 'parents', 
            'jenisSatkers', 'jabatan', 'pegawais', 'jenis_penugasans', 'periodes', 'roles', 
            'userRoles', 'allSatkersFlat', 'refJabatanSatker', 'perm', 'rumusList', 'activePeriodeId'
        ));
    }
    
    private function getAllDescendantIds($satkerId) {
        $satker = Satker::with('childrenRecursive')->find($satkerId);
        if (!$satker) return [];
        
        $ids = [$satkerId];
        $this->extractChildIds($satker->childrenRecursive, $ids);
        return $ids;
    }

    private function extractChildIds($children, &$ids) {
        if (!$children) return;
        foreach ($children as $child) {
            $ids[] = $child->id;
            if ($child->childrenRecursive) {
                $this->extractChildIds($child->childrenRecursive, $ids);
            }
        }
    }

    public function store(Request $request)
    {
        // Validasi Input
        $request->validate([
            'kode_satker_full' => [
                'required',
                \Illuminate\Validation\Rule::unique('satker', 'kode_satker')->where('periode_id', $request->periode_id)
            ],
            'nama_satker'      => 'required|string|max:255',
            'jenis_satker_id'  => 'required|integer',  
            'periode_id'       => 'required|exists:periodes,id',
            'wilayah_id'       => 'required|exists:wilayah,id',
            'parent_satker_id' => 'nullable|exists:satker,id',
            'ref_jabatan_satker_id' => 'nullable|exists:ref_jabatan_satker,id',
            'keterangan'       => 'nullable|string',
        ]);

        // Ambil semua data
        $data = $request->all();

        // Petakan nilai dari input form ke kolom database
        $data['kode_satker'] = $request->kode_satker_full; 
        $data['status_aktif'] = $request->has('status_aktif') ? 1 : 0;
        $data['ref_jabatan_satker_id'] = $request->ref_jabatan_satker_id;

        // 3. Simpan Data
        $satker = Satker::create($data);

        // Catat ke Audit Log
        LogSistem::create([
            'aksi'       => 'CREATE',
            'nama_tabel' => 'satker',
            'data_id'    => $satker->id,
            'perubahan'  => 'Menambahkan satker baru: ' . $satker->nama_satker . ' dengan kode: ' . $satker->kode_satker,
            'user_id'    => auth()->id(),
        ]);
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Satuan Kerja berhasil ditambahkan!',
                'satker_id' => $satker->id
            ]);
        }

        return redirect()->back()->with('success', 'Satuan Kerja berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $satker = Satker::findOrFail($id);
        
        $request->validate([
            'kode_satker'      => [
                'required',
                \Illuminate\Validation\Rule::unique('satker', 'kode_satker')
                    ->where('periode_id', $request->periode_id)
                    ->ignore($id)
            ],
            'nama_satker'      => 'required|string|max:255',
            'jenis_satker_id'  => 'required|integer',  
            'periode_id'       => 'required|exists:periodes,id',
            'wilayah_id'       => 'required|exists:wilayah,id',
            'parent_satker_id' => 'nullable|exists:satker,id',
            'keterangan'       => 'nullable|string',
        ]);

        $data = $request->all();

        // 1. Logika Sinkronisasi Status Aktif
        // Karena checkbox tidak mengirimkan nilai jika tidak dicentang
        $data['status_aktif'] = $request->has('status_aktif') ? 1 : 0;

        unset($data['jenis_satker_id']); 

        // 3. Update Data
        $satker->update($data);

        LogSistem::create([
            'aksi' => 'UPDATE',
            'nama_tabel' => 'satker',
            'data_id' => $satker->id,
            'perubahan' => 'Memperbarui satker: ' . $satker->nama_satker,
            'user_id' => auth()->id(),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Satuan Kerja berhasil diperbarui!',
                'satker_id' => $satker->id
            ]);
        }

        return redirect()->back()->with('success', 'Satuan Kerja berhasil diperbarui');
    }

    public function destroy($id)
    {
        $satker = Satker::findOrFail($id);
        
        if($satker->children()->count() > 0) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Tidak dapat menghapus Satker yang memiliki unit bawahan'], 422);
            }
            return redirect()->back()->withErrors(['error' => 'Tidak dapat menghapus Satker yang memiliki unit bawahan']);
        }

        LogSistem::create([
            'aksi' => 'DELETE',
            'nama_tabel' => 'satker',
            'data_id' => $satker->id,
            'perubahan' => 'Menghapus satker: ' . $satker->nama_satker,
            'user_id' => auth()->id(),
        ]);

        $satker->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Satuan Kerja berhasil dihapus']);
        }
        return redirect()->back()->with('success', 'Satuan Kerja berhasil dihapus');
    }

    public function getUsersBySatker($id)
    {
        // 1. Ambil izin (permissions) orang yang sedang login
        $perm = $this->getPermissions();

        $expiredCuti = \App\Models\Penugasan::with('jenisPenugasan')
            ->where('satker_id', $id)
            ->where('status_aktif', 0)
            ->whereNotNull('tanggal_selesai_cuti')
            ->whereNull('tanggal_selesai') 
            ->whereDate('tanggal_selesai_cuti', '<', now()->toDateString())
            ->get();

        if ($expiredCuti->isNotEmpty()) {
            $hasDefinitifReturning = false;
            foreach ($expiredCuti as $cuti) {
                $cuti->update(['status_aktif' => 1, 'tanggal_mulai_cuti' => null, 'tanggal_selesai_cuti' => null]);
                $namaJenis = $cuti->jenisPenugasan ? strtolower(trim($cuti->jenisPenugasan->nama)) : '';
                if (str_contains($namaJenis, 'definitif')) $hasDefinitifReturning = true;
            }
            if ($hasDefinitifReturning) {
                \App\Models\Penugasan::where('satker_id', $id)->where('status_aktif', 1)
                    ->whereHas('jenisPenugasan', function($q) { $q->where('nama', 'like', '%plt%')->orWhere('nama', 'like', '%plh%'); })
                    ->update(['status_aktif' => 0, 'tanggal_selesai' => now()]);
            }
        }

        $penugasans = \App\Models\Penugasan::with(['user.userDetail', 'user.roles', 'jenisPenugasan'])
            ->where('satker_id', $id)
            ->orderBy('status_aktif', 'desc') 
            ->orderBy('tanggal_mulai', 'desc') 
            ->get()
            ->map(function ($penugasan) use ($perm) {
                $user = $penugasan->user;
                
                // LOGIKA PEMISAHAN TAMPILAN ROLE
                $rolesTertampil = '-';
                if (!empty($penugasan->jenis_penugasan_id)) {
                    // Jika memiliki ID Jenis Penugasan (Definitif/PLT), maka di baris ini dia adalah Pejabat
                    $rolesTertampil = 'Pejabat';
                } else {
                    // Jika kosong, maka di baris ini dia adalah Admin (Ambil role selain pejabat)
                    $adminRoles = ($user && $user->roles) ? $user->roles->where('key', '!=', 'pejabat')->pluck('nama')->toArray() : [];
                    $rolesTertampil = !empty($adminRoles) ? implode(', ', $adminRoles) : 'Admin/Sistem';
                }

                $isCuti = ($penugasan->status_aktif == 0 && $penugasan->tanggal_mulai_cuti);
                $isSelf = (Auth::id() == $penugasan->user_id);

                // =========================================================================
                // CEK HAK AKSI (End Date & Cuti) SECARA DINAMIS
                // =========================================================================
                $canEnd = $perm['is_super'] || $perm['all_access'] || ($isSelf ? in_array('end_self', $perm['actions']) : in_array('end_other', $perm['actions']));
                $canCuti = $perm['is_super'] || $perm['all_access'] || ($isSelf ? in_array('cuti_self', $perm['actions']) : in_array('cuti_other', $perm['actions']));

                return [
                    'penugasan_id'      => $penugasan->id,
                    'name'              => $user ? $user->name : 'Tanpa Nama',
                    'nip'               => $user ? $user->nip : '-',
                    'email'             => $user ? $user->email : '-',
                    'jabatan'           => ($user && $user->userDetail) ? $user->userDetail->tampil_jabatan : '-',
                    'roles'             => $rolesTertampil,
                    'jenis_penugasan'   => $penugasan->jenisPenugasan ? $penugasan->jenisPenugasan->nama : '-',
                    'status_aktif'      => $penugasan->status_aktif ?? 0,
                    
                    'is_cuti'           => $isCuti,
                    'is_self'           => $isSelf,
                    'can_end'           => $canEnd,   // <-- DIKIRIM KE JAVASCRIPT
                    'can_cuti'          => $canCuti,  // <-- DIKIRIM KE JAVASCRIPT
                    
                    'tanggal_mulai_cuti_raw'   => $penugasan->tanggal_mulai_cuti ? \Carbon\Carbon::parse($penugasan->tanggal_mulai_cuti)->format('d F Y') : null,
                    'tanggal_selesai_cuti_raw' => $penugasan->tanggal_selesai_cuti ? \Carbon\Carbon::parse($penugasan->tanggal_selesai_cuti)->format('d F Y') : null,
                    'tanggal_mulai'     => $penugasan->tanggal_mulai ? \Carbon\Carbon::parse($penugasan->tanggal_mulai)->format('d-m-Y') : '-',
                    'tanggal_selesai'   => $penugasan->tanggal_selesai ? \Carbon\Carbon::parse($penugasan->tanggal_selesai)->format('d-m-Y') : '-',
                ];
            });

        return response()->json($penugasans);
    }

    public function generateCode(Request $request)
    {
        $jenisId = $request->jenis_id;
        $parentId = $request->filled('parent_id') && $request->parent_id !== 'null' ? $request->parent_id : null;
        $refJabatanId = $request->ref_jabatan_satker_id;
        $rumusId = $request->rumus_id; 
        $rumpunFakultas = $request->rumpun_fakultas;

        if ($request->filled('jabatan_id')) {
            $jabatan = \App\Models\Jabatan::find($request->jabatan_id);
            if ($jabatan) {
                // Hapus embel-embel jenjang untuk nama default
                $baseName = preg_replace('/\s+(Ahli Pertama|Ahli Muda|Ahli Madya|Ahli Utama|Pemula|Terampil|Mahir|Penyelia)$/i', '', $jabatan->nama_jabatan);
                
                return response()->json([
                    'success' => true,
                    'code' => trim($jabatan->kode_jabatan),
                    'gaps' => [],
                    'default_nama' => trim($baseName),
                    'is_incremental' => false,
                    'is_new' => true,
                    'last_kode' => null,
                    'last_nama' => null
                ]);
            }
        }
        
        $wilayahId = $request->wilayah_id;
        $tingkatWilayahId = null;
        $kodeWilayah = '';
        
        if ($wilayahId) {
            $wilayah = \App\Models\Wilayah::find($wilayahId);
            if ($wilayah) {
                $tingkatWilayahId = $wilayah->tingkat_wilayah_id;
                $kodeWilayah = $wilayah->kode_wilayah; 
            }
        }

        $refJabatan = $refJabatanId ? \App\Models\RefJabatanSatker::find($refJabatanId) : null;

        // 1. CARI RUMUS
        $setup = null;
        $isSpecificSetup = false; // Menandakan apakah ini rumus khusus untuk jabatan ini

        if ($rumusId) {
            $setup = DB::table('rumus_kodes')->where('id', $rumusId)->first();
            $isSpecificSetup = true;
        } else {
            $setup = DB::table('rumus_kodes')
                ->where('is_applied', 1)
                ->where(function ($q) use ($tingkatWilayahId) {
                    $q->where('tingkat_wilayah_id', $tingkatWilayahId)->orWhereNull('tingkat_wilayah_id');
                })
                ->where(function ($q) use ($jenisId) {
                    $q->where('jenis_satker_id', $jenisId)->orWhereNull('jenis_satker_id');
                })
                ->where(function ($q) use ($refJabatanId) {
                    if ($refJabatanId) {
                        $q->where('ref_jabatan_satker_id', $refJabatanId)->orWhereNull('ref_jabatan_satker_id');
                    } else {
                        $q->whereNull('ref_jabatan_satker_id');
                    }
                })
                ->orderByRaw('(CASE WHEN ref_jabatan_satker_id IS NOT NULL THEN 100 ELSE 0 END) + 
                              (CASE WHEN tingkat_wilayah_id IS NOT NULL THEN 10 ELSE 0 END) + 
                              (CASE WHEN jenis_satker_id IS NOT NULL THEN 1 ELSE 0 END) DESC')
                ->first();

            // Cek apakah rumus yang terpilih ini benar-benar ditujukan spesifik untuk jabatan ini
            if ($setup && $setup->ref_jabatan_satker_id == $refJabatanId && $refJabatanId != null) {
                $isSpecificSetup = true;
            }
        }

        if (!$setup) {
            return response()->json(['error' => 'Setup Rumus tidak ditemukan untuk kombinasi ini.'], 404);
        }

        $kodeBaru = "";
        // JAWABAN POIN 2: Prioritaskan Rumpun Fakultas jika terpilih
        if ($rumpunFakultas) {
            $kodeBaru = '[PARENT]' . $rumpunFakultas;
        } elseif ($setup) {
            $kodeBaru = $setup->pola;
        }

        // ===============================================================================
        // 🟢 JEMBATAN KODE DASAR (FIX BUG "TIDAK ADA JABATAN 00")
        // ===============================================================================
        // Jika tidak ada rumus spesifik, tapi jabatan punya kode bawaan (misal: "00", "01")
        if (!$isSpecificSetup && $refJabatan && $refJabatan->kode_dasar) {
            // Timpa pola Sapu Jagat dengan kode paten dari tabel ref_jabatan_satker
            $kodeBaru = '[PARENT]' . $refJabatan->kode_dasar;
            
            // Jika ternyata di database jabatan ini butuh increment (walau jarang)
            if ($refJabatan->is_increment) {
                $kodeBaru .= '[INC:2, START:01]';
            }
        }
        // ===============================================================================

        $parentCode = '';
        $kodeJf = '';

        // Replace TAG PARENT
        if (str_contains($kodeBaru, '[PARENT]')) {
            $parent = Satker::find($parentId);
            $parentCode = $parent ? $parent->kode_satker : '';
            $kodeBaru = str_replace('[PARENT]', $parentCode, $kodeBaru);
        }

        // Replace TAG WILAYAH
        if (str_contains($kodeBaru, '[KODE_WILAYAH]')) {
            $kodeBaru = str_replace('[KODE_WILAYAH]', $kodeWilayah, $kodeBaru);
        }

        // Replace TAG JF
        if (str_contains($kodeBaru, '[KODE_JF]')) {
            $jabatanFungsionalId = $request->jabatan_id; 
            $jf = \App\Models\JabatanFungsional::find($jabatanFungsionalId);
            $kodeJf = $jf ? $jf->kode : '';
            $kodeBaru = str_replace('[KODE_JF]', $kodeJf, $kodeBaru);
        }

        // --- ENGINE INCREMENT & START NUMBER BERTINGKAT ---
        $gaps = []; 
        $isIncremental = false;
        $isNew = true;
        $lastKode = null;
        $lastNama = null;
        $nextNum = 0;
        $maxNum = 0;
        $isDifferentFormula = false;
        $isSameStart = false;
        
        // Membaca pola [INC:digit] ATAU [INC:digit, START:angka]
        if (preg_match('/\[INC:(\d+)(?:,\s*START:(\d+))?\]/', $kodeBaru, $matches)) {
            $isIncremental = true;
            $digit = (int)$matches[1];
            $startLimit = isset($matches[2]) ? (int)$matches[2] : 1; // Default mulai dari 1 jika tidak diatur

            // Pecah pola untuk mencari awalan kode di database
            $prefixPattern = explode('[INC', $kodeBaru)[0];

            $query = Satker::where('kode_satker', 'like', $prefixPattern . '%');
            if ($parentId) {
                $query->where('parent_satker_id', $parentId);
            } else {
                $query->whereNull('parent_satker_id');
            }

            if ($request->periode_id) {
                $query->where('periode_id', $request->periode_id);
            }
            
            // KODE YANG DIUBAH: Ambil seluruh baris untuk mendapatkan "nama_satker"
            $existingSatkers = $query->get(['kode_satker', 'nama_satker']);

            $existingNums = [];
            $expectedLength = strlen($prefixPattern) + $digit;

            foreach ($existingSatkers as $s) {
                $c = trim($s->kode_satker);
                if (strlen($c) === $expectedLength) {
                    $numPart = substr($c, -$digit);
                    if (is_numeric($numPart)) {
                        // Simpan objek utuh berdasarkan urutan angkanya
                        $existingNums[intval($numPart)] = $s;
                    }
                }
            }
            
            $existingKeys = array_keys($existingNums);
            sort($existingKeys);

            $maxNum = !empty($existingKeys) ? max($existingKeys) : 0;

            // CEK SATKER TERAKHIR
            if ($maxNum > 0 && isset($existingNums[$maxNum])) {
                $isNew = false;
                $lastKode = $existingNums[$maxNum]->kode_satker;
                $lastNama = $existingNums[$maxNum]->nama_satker;
            }

            // Jika ada request custom start number (ex: Balai mulai dari 11/31)
            if ($request->has('start_num') && is_numeric($request->start_num)) {
                $customStart = (int) $request->start_num - 1;
                $maxNum = max($maxNum, $customStart);
            }

            // Jika Max Num di DB masih lebih kecil dari Start Limit aturan Kemenag
            if ($maxNum < $startLimit) {
                $maxNum = $startLimit - 1;
            }

            $loopStart = 1;
            if ($request->has('start_num') && is_numeric($request->start_num)) {
                $loopStart = (int) $request->start_num;
            } else {
                $loopStart = $startLimit;
            }

            // --- PERBAIKAN FEEDBACK 1: Cari nomor terkecil yang tersedia ---
            $nextNum = $loopStart;
            while (in_array($nextNum, $existingKeys)) {
                $nextNum++;
            }

            // Hitung Gap (Nomor Bolong) adalah sisa ruang kosong setelah nextNum hingga maxNum
            for ($i = $nextNum + 1; $i < $maxNum; $i++) {
                if (!in_array($i, $existingKeys)) {
                    $gaps[] = $prefixPattern . str_pad($i, $digit, '0', STR_PAD_LEFT);
                }
            }

            // PERBAIKAN: Deteksi status untuk membedakan pesan di frontend
            if (!$isNew && $setup) {
                $rumusName = $setup->nama_rumus ?? '';
                // Jika nama rumus tidak ditemukan pada nama satker terakhir, anggap rumus berbeda
                if ($rumusName && stripos($lastNama, $rumusName) === false) {
                    $isDifferentFormula = true;
                }
                
                // Jika rumus berbeda, tapi harus lompat angka (karena start_num-nya sudah terpakai)
                if ($isDifferentFormula && $nextNum > $loopStart) {
                    $isSameStart = true;
                }
            }

            $incStr = str_pad($nextNum, $digit, '0', STR_PAD_LEFT);
            $kodeBaru = str_replace($matches[0], $incStr, $kodeBaru);
        }

        // Kosongkan opsi "kode kosong/gap" jika rumus BUKAN yang aktif
        if ($setup && isset($setup->is_applied) && $setup->is_applied == 0) {
            $gaps = []; 
        }

        // KODE YANG DIUBAH: Tambahkan variabel respon baru
        return response()->json([
            'code' => $kodeBaru,
            'gaps' => $gaps,
            'default_nama' => $setup->default_nama_satker ?? $setup->nama_rumus ?? '',
            'is_incremental' => $isIncremental,
            'is_new' => $isNew,
            'last_kode' => $lastKode,
            'last_nama' => $lastNama,
            'next_num' => $nextNum,
            'max_num' => $maxNum,
            'is_different_formula' => $isDifferentFormula,
            'is_same_start' => $isSameStart
        ]);
    }
}