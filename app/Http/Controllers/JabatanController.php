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
            
            if (strlen($kodeUtuh) >= 4) {
                $prefix = substr($kodeUtuh, 0, 3);
                $suffix = substr($kodeUtuh, 3);
                $baseName = preg_replace('/\s+(Pemula|Terampil|Mahir|Penyelia|Ahli Pertama|Ahli Muda|Ahli Madya|Ahli Utama)$/i', '', $namaUtuh);

                if (!isset($groupedData[$prefix])) {
                    $groupedData[$prefix] = [
                        'kode' => $prefix,
                        'nama_jabatan' => trim($baseName),
                        'jenjangs' => []
                    ];
                }

                // KUNCI PERBAIKAN: Masukkan semua 12 kolom baseline ke sini agar bisa dibaca Javascript
                $groupedData[$prefix]['jenjangs'][] = [
                    'id' => $j->id,
                    'kode' => $kodeUtuh,
                    'nama_lengkap' => $namaUtuh,
                    'kode_ujung' => $suffix,
                    'baseline' => $j->baseline ?? 0,
                    'b_pertama_menpan'    => $j->b_pertama_menpan,
                    'b_muda_menpan'       => $j->b_muda_menpan,
                    'b_madya_menpan'      => $j->b_madya_menpan,
                    'b_utama_menpan'      => $j->b_utama_menpan,
                    'b_pertama_eksisting' => $j->b_pertama_eksisting,
                    'b_muda_eksisting'    => $j->b_muda_eksisting,
                    'b_madya_eksisting'   => $j->b_madya_eksisting,
                    'b_utama_eksisting'   => $j->b_utama_eksisting,
                    'b_pertama_lowongan'  => $j->b_pertama_lowongan,
                    'b_muda_lowongan'     => $j->b_muda_lowongan,
                    'b_madya_lowongan'    => $j->b_madya_lowongan,
                    'b_utama_lowongan'    => $j->b_utama_lowongan,
                    // EXTRA J5 - J8 (Agar terbaca di Frontend)
                    'b_lima_menpan'       => $j->b_lima_menpan,
                    'b_enam_menpan'       => $j->b_enam_menpan,
                    'b_tujuh_menpan'      => $j->b_tujuh_menpan,
                    'b_delapan_menpan'    => $j->b_delapan_menpan,
                    'b_lima_eksisting'    => $j->b_lima_eksisting,
                    'b_enam_eksisting'    => $j->b_enam_eksisting,
                    'b_tujuh_eksisting'   => $j->b_tujuh_eksisting,
                    'b_delapan_eksisting' => $j->b_delapan_eksisting,
                    'b_lima_lowongan'     => $j->b_lima_lowongan,
                    'b_enam_lowongan'     => $j->b_enam_lowongan,
                    'b_tujuh_lowongan'    => $j->b_tujuh_lowongan,
                    'b_delapan_lowongan'  => $j->b_delapan_lowongan,
                ];
            }
        }

        $jabatans = collect(array_values($groupedData));
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
                $baseName = preg_replace('/\s+(Pemula|Terampil|Mahir|Penyelia|Ahli Pertama|Ahli Muda|Ahli Madya|Ahli Utama)$/i', '', $j->nama_jabatan);
                $groupCount = \App\Models\Jabatan::where('periode_id', $activePeriodeId)->where('kode_jabatan', 'like', $prefix . '%')->count();
                $kategoriLabel = 'Keahlian';
                if ($groupCount > 4) $kategoriLabel = 'Semua Jenjang';
                elseif ((int)$suffix <= 4) $kategoriLabel = 'Keterampilan';

                if (!isset($dropdownGroups[$prefix])) {
                    $dropdownGroups[$prefix] = [
                        'id' => $j->id, 'nama' => trim($baseName), 'kode' => $prefix, 'kategori' => $kategoriLabel
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
            return redirect()->back()->with('error', 'Akses Ditolak');
        }

        $request->validate([
            'periode_id'       => 'required|exists:periodes,id',
            'kode_jabatan'     => 'required|string|max:3', 
            'nama_jabatan'     => 'required|string',
            'jenis_jabatan_id' => 'required',
            'kategori_jenjang' => 'required|in:keterampilan,keahlian,semua',
        ]);

        DB::beginTransaction();
        try {
            $baseName = $request->nama_jabatan; $baseKode = $request->kode_jabatan; $kategori = $request->kategori_jenjang;

            $m_p = (int)$request->b_pertama_menpan; $e_p = (int)$request->b_pertama_eksisting;
            $m_mu = (int)$request->b_muda_menpan;   $e_mu = (int)$request->b_muda_eksisting;
            $m_ma = (int)$request->b_madya_menpan;  $e_ma = (int)$request->b_madya_eksisting;
            $m_u = (int)$request->b_utama_menpan;   $e_u = (int)$request->b_utama_eksisting;

            $m_5 = (int)$request->b_lima_menpan;    $e_5 = (int)$request->b_lima_eksisting;
            $m_6 = (int)$request->b_enam_menpan;    $e_6 = (int)$request->b_enam_eksisting;
            $m_7 = (int)$request->b_tujuh_menpan;   $e_7 = (int)$request->b_tujuh_eksisting;
            $m_8 = (int)$request->b_delapan_menpan; $e_8 = (int)$request->b_delapan_eksisting;

            // KUNCI: Kumpulkan semua data, simpan ke semua baris secara identik
            $baselineData = [
                'b_pertama_menpan' => $m_p, 'b_muda_menpan' => $m_mu, 'b_madya_menpan' => $m_ma, 'b_utama_menpan' => $m_u,
                'b_pertama_eksisting' => $e_p, 'b_muda_eksisting' => $e_mu, 'b_madya_eksisting' => $e_ma, 'b_utama_eksisting' => $e_u,
                'b_pertama_lowongan' => $m_p - $e_p, 'b_muda_lowongan' => $m_mu - $e_mu, 'b_madya_lowongan' => $m_ma - $e_ma, 'b_utama_lowongan' => $m_u - $e_u,
                
                'b_lima_menpan' => $m_5, 'b_enam_menpan' => $m_6, 'b_tujuh_menpan' => $m_7, 'b_delapan_menpan' => $m_8,
                'b_lima_eksisting' => $e_5, 'b_enam_eksisting' => $e_6, 'b_tujuh_eksisting' => $e_7, 'b_delapan_eksisting' => $e_8,
                'b_lima_lowongan' => $m_5 - $e_5, 'b_enam_lowongan' => $m_6 - $e_6, 'b_tujuh_lowongan' => $m_7 - $e_7, 'b_delapan_lowongan' => $m_8 - $e_8,
            ];

            if ($kategori === 'semua') {
                $jenjangs = [['s'=>'1', 'n'=>'Pemula'], ['s'=>'2', 'n'=>'Terampil'], ['s'=>'3', 'n'=>'Mahir'], ['s'=>'4', 'n'=>'Penyelia'], ['s'=>'5', 'n'=>'Ahli Pertama'], ['s'=>'6', 'n'=>'Ahli Muda'], ['s'=>'7', 'n'=>'Ahli Madya'], ['s'=>'8', 'n'=>'Ahli Utama']];
            } elseif ($kategori === 'keterampilan') {
                $jenjangs = [['s'=>'1', 'n'=>'Pemula'], ['s'=>'2', 'n'=>'Terampil'], ['s'=>'3', 'n'=>'Mahir'], ['s'=>'4', 'n'=>'Penyelia']];
            } else {
                $jenjangs = [['s'=>'5', 'n'=>'Ahli Pertama'], ['s'=>'6', 'n'=>'Ahli Muda'], ['s'=>'7', 'n'=>'Ahli Madya'], ['s'=>'8', 'n'=>'Ahli Utama']];
            }

            foreach ($jenjangs as $j) {
                Jabatan::create(array_merge([
                    'periode_id' => $request->periode_id, 'jenis_jabatan_id' => $request->jenis_jabatan_id,
                    'kode_jabatan' => $baseKode . $j['s'], 'nama_jabatan' => $baseName . ' ' . $j['n']
                ], $baselineData));
            }

            DB::commit(); return redirect()->back()->with('success', 'Jabatan berhasil digenerate!');
        } catch (\Exception $e) {
            DB::rollBack(); return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
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
            $satkers = \App\Models\Satker::where('periode_id', $jabatan->periode_id)->orderBy('kode_satker', 'asc')->get();
        }

        $kuotas = \App\Models\DistribusiKuota::where('jabatan_id', $jabatan_id)->get()->keyBy('satker_id');
        $parentIds = $satkers->pluck('parent_satker_id')->filter()->unique()->toArray();

        // AMBIL DATA EKSISTING REALITA
        $prefix = substr($jabatan->kode_jabatan, 0, 3);
        $groupJabatans = Jabatan::where('periode_id', $jabatan->periode_id)->where('kode_jabatan', 'like', $prefix . '%')->get();
        $mapJabatanIds = [];
        foreach($groupJabatans as $gj) {
            if (strlen(trim($gj->kode_jabatan)) >= 4) {
                $mapJabatanIds[substr(trim($gj->kode_jabatan), 3)] = $gj->id;
            }
        }
        $penugasanCounts = \Illuminate\Support\Facades\DB::table('penugasan')
            ->select('satker_id', 'jabatan_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->where('status_aktif', 1)->whereIn('jabatan_id', array_values($mapJabatanIds))
            ->groupBy('satker_id', 'jabatan_id')->get();
            
        $eksMap = []; 
        foreach($penugasanCounts as $pc) {
            $suffix = array_search($pc->jabatan_id, $mapJabatanIds);
            if ($suffix !== false) $eksMap[$pc->satker_id][$suffix] = $pc->total;
        }

        $isSemua = count($mapJabatanIds) > 4;

        $data = $satkers->map(function($satker) use ($kuotas, $isAdminJafung, $parentIds, $eksMap, $isSemua) {
            $kuota = $kuotas->get($satker->id);
            $level = $isAdminJafung ? ($satker->parent_satker_id ? 1 : 0) : max(0, ($satker->jenis_satker_id ?? 1) - 1);
            
            // Logika cerdas: Jika bukan "Semua Jenjang", dan suffix awal adalah '5', jadikan dia kp (kolom 1)
            $e_1 = $eksMap[$satker->id]['1'] ?? ($isSemua ? 0 : ($eksMap[$satker->id]['5'] ?? 0));
            $e_2 = $eksMap[$satker->id]['2'] ?? ($isSemua ? 0 : ($eksMap[$satker->id]['6'] ?? 0));
            $e_3 = $eksMap[$satker->id]['3'] ?? ($isSemua ? 0 : ($eksMap[$satker->id]['7'] ?? 0));
            $e_4 = $eksMap[$satker->id]['4'] ?? ($isSemua ? 0 : ($eksMap[$satker->id]['8'] ?? 0));
            $e_5 = $eksMap[$satker->id]['5'] ?? 0;
            $e_6 = $eksMap[$satker->id]['6'] ?? 0;
            $e_7 = $eksMap[$satker->id]['7'] ?? 0;
            $e_8 = $eksMap[$satker->id]['8'] ?? 0;

            return [
                'id' => $satker->id, 'parent_id' => $satker->parent_satker_id, 'nama_satker' => $satker->nama_satker,
                'level' => $level, 'has_children' => in_array($satker->id, $parentIds),
                
                'kp_menpan' => $kuota->kp_menpan ?? 0, 'kmu_menpan' => $kuota->kmu_menpan ?? 0, 'kma_menpan' => $kuota->kma_menpan ?? 0, 'ku_menpan' => $kuota->ku_menpan ?? 0,
                'k5_menpan' => $kuota->k5_menpan ?? 0, 'k6_menpan'  => $kuota->k6_menpan ?? 0,  'k7_menpan'  => $kuota->k7_menpan ?? 0,  'k8_menpan' => $kuota->k8_menpan ?? 0,
                
                'kp_eksisting' => $kuota->kp_eksisting ?? $e_1, 'kmu_eksisting' => $kuota->kmu_eksisting ?? $e_2, 'kma_eksisting' => $kuota->kma_eksisting ?? $e_3, 'ku_eksisting' => $kuota->ku_eksisting ?? $e_4,
                'k5_eksisting' => $kuota->k5_eksisting ?? $e_5, 'k6_eksisting'  => $kuota->k6_eksisting ?? $e_6,  'k7_eksisting'  => $kuota->k7_eksisting ?? $e_7,  'k8_eksisting' => $kuota->k8_eksisting ?? $e_8,
                
                'kp_lowongan' => ($kuota->kp_menpan ?? 0) - ($kuota->kp_eksisting ?? $e_1),
                'kmu_lowongan' => ($kuota->kmu_menpan ?? 0) - ($kuota->kmu_eksisting ?? $e_2),
                'kma_lowongan' => ($kuota->kma_menpan ?? 0) - ($kuota->kma_eksisting ?? $e_3),
                'ku_lowongan' => ($kuota->ku_menpan ?? 0) - ($kuota->ku_eksisting ?? $e_4),
                
                'k5_lowongan' => ($kuota->k5_menpan ?? 0) - ($kuota->k5_eksisting ?? $e_5),
                'k6_lowongan' => ($kuota->k6_menpan ?? 0) - ($kuota->k6_eksisting ?? $e_6),
                'k7_lowongan' => ($kuota->k7_menpan ?? 0) - ($kuota->k7_eksisting ?? $e_7),
                'k8_lowongan' => ($kuota->k8_menpan ?? 0) - ($kuota->k8_eksisting ?? $e_8),
            ];
        })->values();

        return response()->json([
            'is_semua_jenjang' => $isSemua,
            'b_p_menpan' => $jabatan->b_pertama_menpan, 'b_mu_menpan' => $jabatan->b_muda_menpan, 'b_ma_menpan' => $jabatan->b_madya_menpan, 'b_u_menpan' => $jabatan->b_utama_menpan,
            'b_5_menpan' => $jabatan->b_lima_menpan,    'b_6_menpan' => $jabatan->b_enam_menpan,   'b_7_menpan' => $jabatan->b_tujuh_menpan,   'b_8_menpan' => $jabatan->b_delapan_menpan,
            
            'b_p_eks' => $jabatan->b_pertama_eksisting, 'b_mu_eks' => $jabatan->b_muda_eksisting, 'b_ma_eks' => $jabatan->b_madya_eksisting, 'b_u_eks' => $jabatan->b_utama_eksisting,
            'b_5_eks' => $jabatan->b_lima_eksisting,    'b_6_eks' => $jabatan->b_enam_eksisting,   'b_7_eks' => $jabatan->b_tujuh_eksisting,   'b_8_eks' => $jabatan->b_delapan_eksisting,
            
            'b_p_low' => $jabatan->b_pertama_lowongan,  'b_mu_low' => $jabatan->b_muda_lowongan,  'b_ma_low' => $jabatan->b_madya_lowongan,  'b_u_low' => $jabatan->b_utama_lowongan,
            'b_5_low' => $jabatan->b_lima_lowongan,     'b_6_low' => $jabatan->b_enam_lowongan,   'b_7_low' => $jabatan->b_tujuh_lowongan,   'b_8_low' => $jabatan->b_delapan_lowongan,
            'satkers' => $data
        ]);
    }

    public function saveMatriks(Request $request)
    {
        $request->validate([
            'satker_id'  => 'required|exists:satker,id',
            'jabatan_id' => 'required|exists:jabatan,id',
            'tab_aktif'  => 'required|string', 
        ]);

        $kuota = \App\Models\DistribusiKuota::firstOrNew(['satker_id' => $request->satker_id, 'jabatan_id' => $request->jabatan_id]);

        if ($request->tab_aktif === 'menpan') {
            $kuota->kp_menpan = $request->kp ?? 0; $kuota->kmu_menpan = $request->kmu ?? 0; $kuota->kma_menpan = $request->kma ?? 0; $kuota->ku_menpan = $request->ku ?? 0;
            $kuota->k5_menpan = $request->k5 ?? 0; $kuota->k6_menpan = $request->k6 ?? 0; $kuota->k7_menpan = $request->k7 ?? 0; $kuota->k8_menpan = $request->k8 ?? 0;
        } elseif ($request->tab_aktif === 'eksisting') {
            $kuota->kp_eksisting = $request->kp ?? 0; $kuota->kmu_eksisting = $request->kmu ?? 0; $kuota->kma_eksisting = $request->kma ?? 0; $kuota->ku_eksisting = $request->ku ?? 0;
            $kuota->k5_eksisting = $request->k5 ?? 0; $kuota->k6_eksisting = $request->k6 ?? 0; $kuota->k7_eksisting = $request->k7 ?? 0; $kuota->k8_eksisting = $request->k8 ?? 0;
        }

        // Hitung ulang lowongan (1-8)
        $kuota->kp_lowongan = (int)$kuota->kp_menpan - (int)$kuota->kp_eksisting;
        $kuota->kmu_lowongan = (int)$kuota->kmu_menpan - (int)$kuota->kmu_eksisting;
        $kuota->kma_lowongan = (int)$kuota->kma_menpan - (int)$kuota->kma_eksisting;
        $kuota->ku_lowongan = (int)$kuota->ku_menpan - (int)$kuota->ku_eksisting;

        $kuota->k5_lowongan = (int)$kuota->k5_menpan - (int)$kuota->k5_eksisting;
        $kuota->k6_lowongan = (int)$kuota->k6_menpan - (int)$kuota->k6_eksisting;
        $kuota->k7_lowongan = (int)$kuota->k7_menpan - (int)$kuota->k7_eksisting;
        $kuota->k8_lowongan = (int)$kuota->k8_menpan - (int)$kuota->k8_eksisting;

        $kuota->save();

        return response()->json(['status' => 'success', 'message' => 'Kuota berhasil disimpan']);
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

    public function update(Request $request, $id)
    {
        $perm = $this->getPermissions();
        if (!$perm['is_super'] && !$perm['all_access'] && !in_array('edit', $perm['actions'])) {
            return redirect()->back()->with('error', 'Akses Ditolak: Anda tidak memiliki izin untuk mengubah data.');
        }

        $request->validate([
            'nama_jabatan'          => 'required',
            'jenis_jabatan_id'      => 'required|exists:m_jenis_jabatan,id',
            'jenis_satker_id'       => 'nullable|exists:m_jenis_satker,id',
            'jabatan_fungsional_id' => 'nullable|exists:jabatan_fungsionals,id',
        ]);

        try {
            $jabatan = Jabatan::findOrFail($id);
            $jabatan->update($request->all());

            \App\Models\LogSistem::create([
                'aksi'       => 'UPDATE',
                'nama_tabel' => 'jabatan',
                'data_id'    => $jabatan->id,
                'perubahan'  => 'Memperbarui jabatan: ' . $jabatan->nama_jabatan,
                'user_id'    => auth()->id(),
            ]);

            return redirect()->back()->with('success', 'Jabatan berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function updateGlobal(Request $request)
    {
        $perm = $this->getPermissions();
        if (!$perm['is_super'] && !$perm['all_access'] && !in_array('edit', $perm['actions'])) {
            return redirect()->back()->with('error', 'Akses Ditolak');
        }

        $request->validate(['periode_id' => 'required', 'kode_dasar' => 'required', 'nama_jabatan' => 'required']);

        DB::beginTransaction();
        try {
            $records = Jabatan::where('periode_id', $request->periode_id)->where('kode_jabatan', 'like', $request->kode_dasar . '%')->get();

            $m_p = (int)$request->input('b_pertama_menpan', 0); $e_p = (int)$request->input('b_pertama_eksisting', 0);
            $m_mu = (int)$request->input('b_muda_menpan', 0);   $e_mu = (int)$request->input('b_muda_eksisting', 0);
            $m_ma = (int)$request->input('b_madya_menpan', 0);  $e_ma = (int)$request->input('b_madya_eksisting', 0);
            $m_u = (int)$request->input('b_utama_menpan', 0);   $e_u = (int)$request->input('b_utama_eksisting', 0);

            $m_5 = (int)$request->input('b_lima_menpan', 0);    $e_5 = (int)$request->input('b_lima_eksisting', 0);
            $m_6 = (int)$request->input('b_enam_menpan', 0);    $e_6 = (int)$request->input('b_enam_eksisting', 0);
            $m_7 = (int)$request->input('b_tujuh_menpan', 0);   $e_7 = (int)$request->input('b_tujuh_eksisting', 0);
            $m_8 = (int)$request->input('b_delapan_menpan', 0); $e_8 = (int)$request->input('b_delapan_eksisting', 0);

            $baselineData = [
                'b_pertama_menpan' => $m_p, 'b_muda_menpan' => $m_mu, 'b_madya_menpan' => $m_ma, 'b_utama_menpan' => $m_u,
                'b_pertama_eksisting' => $e_p, 'b_muda_eksisting' => $e_mu, 'b_madya_eksisting' => $e_ma, 'b_utama_eksisting' => $e_u,
                'b_pertama_lowongan' => $m_p - $e_p, 'b_muda_lowongan' => $m_mu - $e_mu, 'b_madya_lowongan' => $m_ma - $e_ma, 'b_utama_lowongan' => $m_u - $e_u,
                
                'b_lima_menpan' => $m_5, 'b_enam_menpan' => $m_6, 'b_tujuh_menpan' => $m_7, 'b_delapan_menpan' => $m_8,
                'b_lima_eksisting' => $e_5, 'b_enam_eksisting' => $e_6, 'b_tujuh_eksisting' => $e_7, 'b_delapan_eksisting' => $e_8,
                'b_lima_lowongan' => $m_5 - $e_5, 'b_enam_lowongan' => $m_6 - $e_6, 'b_tujuh_lowongan' => $m_7 - $e_7, 'b_delapan_lowongan' => $m_8 - $e_8,
            ];

            foreach ($records as $record) {
                $suffix = substr($record->kode_jabatan, 3);
                $jenjangName = '';
                switch ($suffix) {
                    case '1': $jenjangName = 'Pemula'; break; case '2': $jenjangName = 'Terampil'; break; case '3': $jenjangName = 'Mahir'; break; case '4': $jenjangName = 'Penyelia'; break;
                    case '5': $jenjangName = 'Ahli Pertama'; break; case '6': $jenjangName = 'Ahli Muda'; break; case '7': $jenjangName = 'Ahli Madya'; break; case '8': $jenjangName = 'Ahli Utama'; break;
                }
                $record->update(array_merge(['nama_jabatan' => $request->nama_jabatan . ' ' . $jenjangName], $baselineData));
            }

            LogSistem::create(['aksi' => 'UPDATE', 'nama_tabel' => 'jabatan', 'data_id' => $records->first()->id ?? 0, 'perubahan' => 'Edit global jabatan: ' . $request->nama_jabatan, 'user_id' => auth()->id()]);
            DB::commit(); return redirect()->back()->with('success', 'Grup Jabatan berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack(); return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
}