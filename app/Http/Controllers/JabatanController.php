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
        $sortField = $request->input('sort', 'kode_jabatan');
        $sortDirection = $request->input('direction', 'asc');

        $periodes = Periode::orderBy('created_at', 'asc')->get();
        
        $activePeriodeId = $request->input('periode_id', $periodes->where('is_active', true)->first()->id ?? ($periodes->last()->id ?? null));

        $jabatans = Jabatan::with(['jenis', 'jenisSatker', 'fungsional'])
            ->where('periode_id', $activePeriodeId)
            ->when($search, function ($query, $search) {

                return $query->where(function($q) use ($search) {
                    $q->where('nama_jabatan', 'like', "%{$search}%")
                      ->orWhere('kode_jabatan', 'like', "%{$search}%");
                });
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate(10)
            ->withQueryString();

        $jenis_jabatans = MJenisJabatan::all();
        $eselons = MJenisSatker::all();
        $fungsionals = \App\Models\JabatanFungsional::orderBy('name', 'asc')->get();
        $idFungsional = $jenis_jabatans->where('nama', 'Fungsional')->first()->id ?? '';

        // Pastikan generate kode selanjutnya mengacu pada periode yang sama
        $lastJabatan = Jabatan::where('periode_id', $activePeriodeId)
                            ->selectRaw('MAX(SUBSTRING(kode_jabatan, 1, 3)) as base_last')
                            ->first();
        
        $nextBaseCode = $lastJabatan && $lastJabatan->base_last ? (int)$lastJabatan->base_last + 1 : 801;

        // 3. DROPDOWN JUGA DIFILTER BERDASARKAN PERIODE
        $dropdownJabatans = Jabatan::with('fungsional')
            ->where('periode_id', $activePeriodeId)
            ->orderBy('kode_jabatan', 'asc')
            ->get();

        if ($request->ajax()) {
            return view('admin.jabatan.index', compact('jabatans', 'jenis_jabatans', 'eselons', 'fungsionals', 'idFungsional', 'nextBaseCode', 'dropdownJabatans', 'periodes', 'activePeriodeId', 'perm'))->render();
        }

        return view('admin.jabatan.index', compact('jabatans', 'jenis_jabatans', 'eselons', 'fungsionals', 'idFungsional', 'nextBaseCode', 'dropdownJabatans', 'periodes', 'activePeriodeId', 'perm'));
    }

    public function store(Request $request)
    {
        $perm = $this->getPermissions();
        if (!$perm['is_super'] && !$perm['all_access'] && !in_array('create', $perm['actions'])) {
            return redirect()->back()->with('error', 'Akses Ditolak: Anda tidak memiliki izin untuk menambah data.');
        }

        $request->validate([
            'periode_id'            => 'required|exists:periodes,id', // Tambahan validasi periode
            'kode_jabatan'          => 'required', 
            'nama_jabatan'          => 'required',
            'baseline'              => 'required|numeric|min:0', // Tambahan validasi baseline
            'jenis_jabatan_id'      => 'required|exists:m_jenis_jabatan,id',
            'jenis_satker_id'       => 'nullable|exists:m_jenis_satker,id',
            'jabatan_fungsional_id' => 'nullable|exists:jabatan_fungsionals,id',
        ]);

        try {
            $jabatan = Jabatan::create($request->all());

            LogSistem::create([
                'aksi'       => 'CREATE',
                'nama_tabel' => 'jabatan',
                'data_id'    => $jabatan->id, 
                'perubahan'  => 'Menambahkan jabatan: ' . $jabatan->nama_jabatan . ' (Kode: ' . $jabatan->kode_jabatan . ') dengan Baseline: ' . $jabatan->baseline,
                'user_id'    => auth()->id(),
            ]);

            return redirect()->back()->with('success', 'Jabatan berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambah data: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $perm = $this->getPermissions();
        if (!$perm['is_super'] && !$perm['all_access'] && !in_array('edit', $perm['actions'])) {
            return redirect()->back()->with('error', 'Akses Ditolak: Anda tidak memiliki izin untuk mengubah data.');
        }

        $request->validate([
            'nama_jabatan'          => 'required',
            'baseline'              => 'required|numeric|min:0', // Tambahan validasi baseline
            'jenis_jabatan_id'      => 'required|exists:m_jenis_jabatan,id',
            'jenis_satker_id'       => 'nullable|exists:m_jenis_satker,id',
            'jabatan_fungsional_id' => 'nullable|exists:jabatan_fungsionals,id',
        ]);

        try {
            $jabatan = Jabatan::findOrFail($id);
            
            $jabatan->update($request->all());

            LogSistem::create([
                'aksi'       => 'UPDATE',
                'nama_tabel' => 'jabatan',
                'data_id'    => $jabatan->id,
                'perubahan'  => 'Memperbarui jabatan: ' . $jabatan->nama_jabatan . ' (Baseline: ' . $jabatan->baseline . ')',
                'user_id'    => auth()->id(),
            ]);

            return redirect()->back()->with('success', 'Jabatan berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
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
        
        // Deteksi apakah user adalah Admin Jafung
        $isAdminJafung = (in_array('admin_jafung_pengguna', $userRoles) || in_array('admin_jafung_pembina', $userRoles)) && !$isSuperAdmin;

        $satkers = collect();

        // JIKA ADMIN JAFUNG: Hanya tampilkan Satkernya dan Induk Satkernya
        if ($isAdminJafung && $user->satker_id) {
            $userSatker = \App\Models\Satker::where('periode_id', $jabatan->periode_id)
                            ->find($user->satker_id);
                            
            if ($userSatker) {
                if ($userSatker->parent_satker_id) {
                    $parentSatker = \App\Models\Satker::where('periode_id', $jabatan->periode_id)
                                        ->find($userSatker->parent_satker_id);
                    if ($parentSatker) {
                        $satkers->push($parentSatker);
                    }
                }
                $satkers->push($userSatker);
            }
        } 
        // JIKA SUPER ADMIN: Tampilkan seluruh Satker (PERBAIKAN HIERARKI ESELON 1-5)
        else {
            // Kita tarik SEMUA satker di periode ini, dan langsung diurutkan berdasarkan kode_satker.
            // Karena format kode_satker itu berurutan (01, 0101, 010101), ini otomatis menyusun 
            // data layaknya pohon hierarki dari atas ke bawah!
            $satkers = \App\Models\Satker::where('periode_id', $jabatan->periode_id)
                        ->orderBy('kode_satker', 'asc')
                        ->get();
        }

        // Ambil data kuota yang sudah disimpan
        $kuotas = \App\Models\DistribusiKuota::where('jabatan_id', $jabatan_id)->get()->keyBy('satker_id');
        
        // Ambil semua ID parent untuk mengecek mana satker yang punya bawahan (tombol simpan akan muncul)
        $parentIds = $satkers->pluck('parent_satker_id')->filter()->unique()->toArray();
        
        $data = $satkers->map(function($satker) use ($kuotas, $isAdminJafung, $parentIds) {
            $kuota = $kuotas->get($satker->id);
            
            // Menentukan Level Indentasi: Eselon 1 = Level 0, Eselon 2 = Level 1, dst.
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
                'has_children'  => in_array($satker->id, $parentIds), // Tandai jika punya bawahan
                'kuota_pertama' => $kuota ? $kuota->kuota_pertama : 0,
                'kuota_muda'    => $kuota ? $kuota->kuota_muda : 0,
                'kuota_madya'   => $kuota ? $kuota->kuota_madya : 0,
                'kuota_utama'   => $kuota ? $kuota->kuota_utama : 0,
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
}