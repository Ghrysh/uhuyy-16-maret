<?php
namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\MJenisJabatan;
use App\Models\MJenisSatker;
use App\Models\LogSistem;
use App\Models\JabatanFungsional;
use App\Models\Satker;
use App\Models\DistribusiKuota;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JabatanController extends Controller
{

    private function getPermissions()
    {
        $user = auth()->user();
        $userRoles = $user->roles;
        $isSuperAdmin = $userRoles->contains('key', 'super_admin');

        if ($isSuperAdmin) {
            return ['is_super' => true, 'can_view' => true, 'all_access' => true, 'actions' => ['create', 'edit', 'delete'], 'matriks' => ['set_baseline', 'edit_kuota']];
        }

        $permissions = ['is_super' => false, 'can_view' => false, 'all_access' => false, 'view_only' => false, 'actions' => [], 'matriks' => []];

        foreach ($userRoles as $role) {
            $config = [];
            if ($role->key === 'pejabat') {
                $active = \App\Models\Penugasan::where('user_id', $user->id)->where('status_aktif', 1)->with('jenisPenugasan')->first();
                if ($active && $active->jenisPenugasan) {
                    $menus = $active->jenisPenugasan->menus;
                    $config = is_array($menus) ? ($menus['jabatan'] ?? []) : [];
                }
            } else {
                $menus = $role->menus;
                $config = is_array($menus) ? ($menus['jabatan'] ?? []) : [];
            }
            
            if (!empty($config) && ($config['enabled'] ?? false)) {
                $permissions['can_view'] = true;
                if ($config['all_access'] ?? false) $permissions['all_access'] = true;
                if ($config['view_only'] ?? false) $permissions['view_only'] = true;
                
                if (isset($config['actions']) && is_array($config['actions'])) {
                    $permissions['actions'] = array_unique(array_merge($permissions['actions'], $config['actions']));
                }
                if (isset($config['matriks']) && is_array($config['matriks'])) {
                    $permissions['matriks'] = array_unique(array_merge($permissions['matriks'], $config['matriks']));
                }
            }
        }
        return $permissions;
    }

    public function index(Request $request)
    {
        $perm = $this->getPermissions();
        if (!$perm['can_view']) abort(403, 'Akses ditolak. Anda tidak memiliki izin melihat Jabatan Fungsional.');

        $search = $request->input('search');

        $periodes = Periode::orderBy('created_at', 'asc')->get();
        $activePeriodeId = $request->input('periode_id', $periodes->first()->id ?? null);

        // =========================================================================
        // JAWABAN FEEDBACK: MENGELOMPOKKAN DATA MENJADI PARENT & ANAK JENJANG
        // =========================================================================
        $rawJabatansQuery = Jabatan::with(['fungsional'])
            ->where('periode_id', $activePeriodeId);
            
        if ($search) {
            $rawJabatansQuery->where(function($q) use ($search) {
                $q->where('nama_jabatan', 'like', "%{$search}%")
                  ->orWhere('kode_jabatan', 'like', "%{$search}%");
            });
        }
        
        $rawJabatans = $rawJabatansQuery->orderBy('kode_jabatan', 'asc')->get();
        $groupedData = [];

        foreach ($rawJabatans as $j) {
            $kodeUtuh = trim($j->kode_jabatan);
            $namaUtuh = trim($j->nama_jabatan);
            
            // Ekstrak kode dasar dan jenjang
            if (strlen($kodeUtuh) >= 4) {
                $prefix = substr($kodeUtuh, 0, 3);
                $suffix = substr($kodeUtuh, 3);

                // Nama dasar untuk parent group (tanpa embel-embel jenjang)
                $baseName = preg_replace('/\s+(Pemula|Terampil|Mahir|Penyelia|Ahli Pertama|Ahli Muda|Ahli Madya|Ahli Utama)$/i', '', $namaUtuh);

                if (!isset($groupedData[$prefix])) {
                    $groupedData[$prefix] = [
                        'kode' => $prefix,
                        'nama_jabatan' => trim($baseName),
                        'b_pertama' => $j->b_pertama ?? 0,
                        'b_muda' => $j->b_muda ?? 0,
                        'b_madya' => $j->b_madya ?? 0,
                        'b_utama' => $j->b_utama ?? 0,
                        'jenjangs' => []
                    ];
                }

                $groupedData[$prefix]['jenjangs'][] = [
                    'id' => $j->id,
                    'kode' => $kodeUtuh,
                    'nama_lengkap' => $namaUtuh,
                    'kode_ujung' => $suffix,
                    'baseline' => $j->baseline ?? 0 // Tambahkan variabel baseline ini!
                ];
            } else {
                // Fallback untuk data yang tidak standar (< 4 digit)
                $groupedData[$kodeUtuh] = [
                    'kode' => $kodeUtuh,
                    'nama_jabatan' => $namaUtuh,
                    'jenjangs' => [[
                        'id' => $j->id,
                        'kode' => $kodeUtuh,
                        'nama_lengkap' => $namaUtuh,
                        'kode_ujung' => '-',
                        'baseline' => $j->baseline ?? 0 // Tambahkan ini!
                    ]]
                ];
            }
        }

        // Paginasi manual untuk Array hasil grouping
        $jabatansCollection = collect(array_values($groupedData));
        $perPage = 10;
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
        $currentPageItems = $jabatansCollection->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $jabatans = new \Illuminate\Pagination\LengthAwarePaginator($currentPageItems, count($jabatansCollection), $perPage, $currentPage, [
            'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()
        ]);
        // =========================================================================

        $jenis_jabatans = MJenisJabatan::all();
        $idFungsional = $jenis_jabatans->where('nama', 'Fungsional')->first()->id ?? '';

        $lastJabatan = Jabatan::where('periode_id', $activePeriodeId)
                            ->selectRaw('MAX(CAST(SUBSTRING(kode_jabatan, 1, 3) AS INTEGER)) as base_last')
                            ->first();

        $nextBaseCode = $lastJabatan && $lastJabatan->base_last ? (int)$lastJabatan->base_last + 1 : 801;

        $rawDropdown = Jabatan::where('periode_id', $activePeriodeId)
            ->orderBy('kode_jabatan', 'asc')
            ->get();

        $dropdownGroups = [];
        foreach ($rawDropdown as $j) {
            $kodeUtuh = trim($j->kode_jabatan);
            if (strlen($kodeUtuh) >= 4) {
                $prefix = substr($kodeUtuh, 0, 3);
                $suffix = substr($kodeUtuh, 3);
                // Bersihkan nama dari embel-embel jenjang
                $baseName = preg_replace('/\s+(Pemula|Terampil|Mahir|Penyelia|Ahli Pertama|Ahli Muda|Ahli Madya|Ahli Utama)$/i', '', $j->nama_jabatan);

                if (!isset($dropdownGroups[$prefix])) {
                    $dropdownGroups[$prefix] = [
                        'id' => $j->id, // ID Jenjang pertama sebagai perwakilan grup
                        'nama' => trim($baseName),
                        'kode' => $prefix,
                        'kategori' => (int)$suffix <= 4 ? 'Keterampilan' : 'Keahlian'
                    ];
                }
            }
        }
        $dropdownJabatans = array_values($dropdownGroups);

        if ($request->ajax()) {
            return view('admin.jabatan.index', compact('jabatans', 'jenis_jabatans', 'idFungsional', 'nextBaseCode', 'dropdownJabatans', 'periodes', 'activePeriodeId', 'perm'))->render();
        }

        return view('admin.jabatan.index', compact('jabatans', 'jenis_jabatans', 'idFungsional', 'nextBaseCode', 'dropdownJabatans', 'periodes', 'activePeriodeId', 'perm'));
    }

    public function store(Request $request)
    {
        $perm = $this->getPermissions();
        if (!$perm['is_super'] && !$perm['all_access'] && !in_array('create', $perm['actions'])) {
            return redirect()->back()->with('error', 'Akses Ditolak: Anda tidak memiliki izin untuk menambah data.');
        }

        $request->validate([
            'periode_id'       => 'required|exists:periodes,id',
            'kode_jabatan'     => 'required|string|max:10', // Max digit aman
            'nama_jabatan'     => 'required|string|max:255',
            'kategori_jenjang' => 'required|in:keterampilan,keahlian', // Validasi dari radio button
        ]);

        $baseKode = $request->kode_jabatan;
        $baseName = $request->nama_jabatan;
        $kategori = $request->kategori_jenjang;
        $periodeId = $request->periode_id;

        // Ambil ID fungsional jika diperlukan dari tabel jenis (opsional)
        $jenis_jabatans = MJenisJabatan::all();
        $idFungsional = $jenis_jabatans->where('nama', 'Fungsional')->first()->id ?? null;

        $b_pertama = $request->input('b_pertama', 0);
        $b_muda    = $request->input('b_muda', 0);
        $b_madya   = $request->input('b_madya', 0);
        $b_utama   = $request->input('b_utama', 0);

        // =========================================================================
        // JAWABAN FEEDBACK: GENERATE 4 DATA SEKALIGUS BESERTA BASELINE-NYA
        // =========================================================================
        $jenjangData = [];
        if ($kategori === 'keterampilan') {
            $jenjangData = [
                ['kode_ujung' => '1', 'jenjang_name' => 'Pemula', 'base_val' => $b_pertama],
                ['kode_ujung' => '2', 'jenjang_name' => 'Terampil', 'base_val' => $b_muda],
                ['kode_ujung' => '3', 'jenjang_name' => 'Mahir', 'base_val' => $b_madya],
                ['kode_ujung' => '4', 'jenjang_name' => 'Penyelia', 'base_val' => $b_utama],
            ];
        } else if ($kategori === 'keahlian') {
            $jenjangData = [
                ['kode_ujung' => '5', 'jenjang_name' => 'Ahli Pertama', 'base_val' => $b_pertama],
                ['kode_ujung' => '6', 'jenjang_name' => 'Ahli Muda', 'base_val' => $b_muda],
                ['kode_ujung' => '7', 'jenjang_name' => 'Ahli Madya', 'base_val' => $b_madya],
                ['kode_ujung' => '8', 'jenjang_name' => 'Ahli Utama', 'base_val' => $b_utama],
            ];
        }

        DB::beginTransaction();
        try {
            foreach ($jenjangData as $j) {
                $fullCode = $baseKode . $j['kode_ujung'];
                $fullName = $baseName . ' ' . $j['jenjang_name'];

                $exists = Jabatan::where('kode_jabatan', $fullCode)->where('periode_id', $periodeId)->exists();
                
                if (!$exists) {
                    Jabatan::create([
                        'id' => (string) \Illuminate\Support\Str::uuid(),
                        'periode_id' => $periodeId,
                        'kode_jabatan' => $fullCode,
                        'nama_jabatan' => $fullName,
                        'jenis_jabatan_id' => $idFungsional,
                        'baseline' => $j['base_val'], // <-- Menyimpan Baseline Spesifik
                        'b_pertama' => $b_pertama,
                        'b_muda' => $b_muda,
                        'b_madya' => $b_madya,
                        'b_utama' => $b_utama,
                    ]);
                }
            }

            LogSistem::create([
                'aksi'       => 'CREATE',
                'nama_tabel' => 'jabatan',
                'data_id'    => $baseKode, 
                'perubahan'  => 'MENG-GENERATE massal jabatan fungsional: ' . $baseName . ' (Kategori: ' . $kategori . ') untuk 4 jenjang sekaligus.',
                'user_id'    => auth()->id(),
            ]);

            DB::commit();
            return redirect()->back()->with('success', '4 Jenjang Jabatan Fungsional berhasil di-generate secara otomatis!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal men-generate data: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $perm = $this->getPermissions();
        if (!$perm['is_super'] && !$perm['all_access'] && !in_array('delete', $perm['actions'])) {
            return redirect()->back()->with('error', 'Akses Ditolak: Anda tidak memiliki izin untuk menghapus data.');
        }

        try {
            $jabatan = Jabatan::findOrFail($id);

            LogSistem::create([
                'aksi' => 'DELETE',
                'nama_tabel' => 'jabatan',
                'data_id' => $jabatan->id,
                'perubahan' => 'Menghapus jabatan: ' . $jabatan->nama_jabatan,
                'user_id' => auth()->id(),
            ]);

            $jabatan->delete();
            return redirect()->back()->with('success', 'Jabatan berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data');
        }
    }
    
    public function getMatriks(Request $request)
    {
        $jabatan_id = $request->query('jabatan_id'); 
        $jabatan = Jabatan::findOrFail($jabatan_id);
        
        $user = \Illuminate\Support\Facades\Auth::user();
        $userRoles = $user->roles()->pluck('key')->toArray();
        $isSuperAdmin = in_array('super_admin', $userRoles);
        
        $isAdminJafung = (in_array('admin_jafung_pengguna', $userRoles) || in_array('admin_jafung_pembina', $userRoles)) && !$isSuperAdmin;

        $satkers = collect();

        if ($isAdminJafung && $user->satker_id) {
            $userSatker = \App\Models\Satker::where('periode_id', $jabatan->periode_id)->find($user->satker_id);
            if ($userSatker) {
                if ($userSatker->parent_satker_id) {
                    $parentSatker = \App\Models\Satker::where('periode_id', $jabatan->periode_id)->find($userSatker->parent_satker_id);
                    if ($parentSatker) $satkers->push($parentSatker);
                }
                $satkers->push($userSatker);
            }
        } else {
            $satkers = \App\Models\Satker::where('periode_id', $jabatan->periode_id)
                        ->orderBy('kode_satker', 'asc')
                        ->get();
        }

        $kuotas = \App\Models\DistribusiKuota::where('jabatan_id', $jabatan_id)->get()->keyBy('satker_id');
        $parentIds = $satkers->pluck('parent_satker_id')->filter()->unique()->toArray();

        // =========================================================
        // TAMBAHAN FEEDBACK: Hitung data Pegawai Eksisting (Real)
        // =========================================================
        $prefix = substr($jabatan->kode_jabatan, 0, 3);
        $groupJabatans = Jabatan::where('periode_id', $jabatan->periode_id)
            ->where('kode_jabatan', 'like', $prefix . '%')
            ->get();
            
        $mapJabatanIds = [];
        foreach($groupJabatans as $gj) {
            if (strlen(trim($gj->kode_jabatan)) >= 4) {
                $mapJabatanIds[substr(trim($gj->kode_jabatan), 3)] = $gj->id;
            }
        }

        $penugasanCounts = \Illuminate\Support\Facades\DB::table('penugasan')
            ->select('satker_id', 'jabatan_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->where('status_aktif', 1)
            ->whereIn('jabatan_id', array_values($mapJabatanIds))
            ->groupBy('satker_id', 'jabatan_id')
            ->get();
            
        $eksMap = []; 
        foreach($penugasanCounts as $pc) {
            $suffix = array_search($pc->jabatan_id, $mapJabatanIds);
            if ($suffix !== false) {
                $eksMap[$pc->satker_id][$suffix] = $pc->total;
            }
        }
        // =========================================================

        $data = $satkers->map(function($satker) use ($kuotas, $isAdminJafung, $parentIds, $eksMap) {
            $kuota = $kuotas->get($satker->id);
            
            if ($isAdminJafung) {
                $level = $satker->parent_satker_id ? 1 : 0; 
            } else {
                $level = max(0, ($satker->jenis_satker_id ?? 1) - 1);
            }
            
            return [
                'id'            => $satker->id,
                'parent_id'     => $satker->parent_satker_id,
                'nama_satker'   => $satker->nama_satker,
                'level'         => $level,
                'has_children'  => in_array($satker->id, $parentIds),
                'kuota_pertama' => $kuota ? $kuota->kuota_pertama : 0,
                'kuota_muda'    => $kuota ? $kuota->kuota_muda : 0,
                'kuota_madya'   => $kuota ? $kuota->kuota_madya : 0,
                'kuota_utama'   => $kuota ? $kuota->kuota_utama : 0,
                // Inject jumlah real (eksisting)
                'eks_pertama'   => max($eksMap[$satker->id]['1'] ?? 0, $eksMap[$satker->id]['5'] ?? 0),
                'eks_muda'      => max($eksMap[$satker->id]['2'] ?? 0, $eksMap[$satker->id]['6'] ?? 0),
                'eks_madya'     => max($eksMap[$satker->id]['3'] ?? 0, $eksMap[$satker->id]['7'] ?? 0),
                'eks_utama'     => max($eksMap[$satker->id]['4'] ?? 0, $eksMap[$satker->id]['8'] ?? 0),
            ];
        })->values();

        return response()->json([
            'baseline'  => $jabatan->baseline,
            'b_pertama' => $jabatan->b_pertama,
            'b_muda'    => $jabatan->b_muda,
            'b_madya'   => $jabatan->b_madya,
            'b_utama'   => $jabatan->b_utama,
            'satkers'   => $data
        ]);
    }

    public function saveBaselineJenjang(Request $request)
    {
        $request->validate([
            'jabatan_id' => 'required|exists:jabatan,id',
            'b_pertama'  => 'numeric',
            'b_muda'     => 'numeric',
            'b_madya'    => 'numeric',
            'b_utama'    => 'numeric',
        ]);

        $jabatan = Jabatan::findOrFail($request->jabatan_id);
        $totalInput = $request->b_pertama + $request->b_muda + $request->b_madya + $request->b_utama;

        if($totalInput > $jabatan->baseline) {
            return response()->json(['status' => 'error', 'message' => 'Total rincian melebihi Grand Total Kuota!']);
        }

        $jabatan->update([
            'b_pertama' => $request->b_pertama,
            'b_muda'    => $request->b_muda,
            'b_madya'   => $request->b_madya,
            'b_utama'   => $request->b_utama,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Baseline per jenjang berhasil disimpan']);
    }

    public function saveMatriks(Request $request)
    {
        $request->validate([
            'satker_id'     => 'required|exists:satker,id',
            'jabatan_id'    => 'required|exists:jabatan,id',
            'kuota_pertama' => 'numeric',
            'kuota_muda'    => 'numeric',
            'kuota_madya'   => 'numeric',
            'kuota_utama'   => 'numeric',
        ]);

        DistribusiKuota::updateOrCreate(
            [
                'satker_id'  => $request->satker_id,
                'jabatan_id' => $request->jabatan_id
            ],
            [
                'kuota_pertama' => $request->kuota_pertama ?? 0,
                'kuota_muda'    => $request->kuota_muda ?? 0,
                'kuota_madya'   => $request->kuota_madya ?? 0,
                'kuota_utama'   => $request->kuota_utama ?? 0,
            ]
        );

        return response()->json(['status' => 'success', 'message' => 'Kuota berhasil disimpan']);
    }

    public function updateGlobal(Request $request)
    {
        $request->validate([
            'periode_id'   => 'required|exists:periodes,id',
            'kode_dasar'   => 'required|string|max:3',
            'nama_jabatan' => 'required|string|max:255',
            'b_pertama'    => 'required|numeric|min:0',
            'b_muda'       => 'required|numeric|min:0',
            'b_madya'      => 'required|numeric|min:0',
            'b_utama'      => 'required|numeric|min:0',
        ]);

        $prefix = $request->kode_dasar;
        $periodeId = $request->periode_id;
        $baseName = $request->nama_jabatan;

        DB::beginTransaction();
        try {
            $records = Jabatan::where('periode_id', $periodeId)
                ->where('kode_jabatan', 'like', $prefix . '%')
                ->get();

            foreach ($records as $record) {
                $suffix = substr($record->kode_jabatan, 3);
                $jenjangName = '';
                $specificBaseline = 0;
                
                switch ($suffix) {
                    case '1': $jenjangName = 'Pemula'; $specificBaseline = $request->b_pertama; break;
                    case '2': $jenjangName = 'Terampil'; $specificBaseline = $request->b_muda; break;
                    case '3': $jenjangName = 'Mahir'; $specificBaseline = $request->b_madya; break;
                    case '4': $jenjangName = 'Penyelia'; $specificBaseline = $request->b_utama; break;
                    case '5': $jenjangName = 'Ahli Pertama'; $specificBaseline = $request->b_pertama; break;
                    case '6': $jenjangName = 'Ahli Muda'; $specificBaseline = $request->b_muda; break;
                    case '7': $jenjangName = 'Ahli Madya'; $specificBaseline = $request->b_madya; break;
                    case '8': $jenjangName = 'Ahli Utama'; $specificBaseline = $request->b_utama; break;
                }

                $record->update([
                    'nama_jabatan' => $baseName . ' ' . $jenjangName,
                    'baseline'     => $specificBaseline,
                    'b_pertama'    => $request->b_pertama,
                    'b_muda'       => $request->b_muda,
                    'b_madya'      => $request->b_madya,
                    'b_utama'      => $request->b_utama,
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Grup Jabatan berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }
}