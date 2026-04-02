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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class SatkerController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('key')->toArray();

        $isSuperAdmin = in_array('super_admin', $userRoles);
        $isRestricted = (in_array('admin_satker', $userRoles) || in_array('pejabat', $userRoles)) && !$isSuperAdmin;
        $userSatkerId = $user->satker_id;

        $satkerQuery = Satker::with('children');
        $flatQuery = Satker::with(['childrenRecursive', 'wilayah', 'eselon']);
        $tableQuery = Satker::query()->with(['wilayah', 'eselon']);
        $listQuery = Satker::select('id', 'nama_satker', 'kode_satker', 'jenis_satker_id', 'periode_id');
        $parentsQuery = Satker::query();

        if ($isRestricted && $userSatkerId) {
            $descendantIds = $this->getAllDescendantIds($userSatkerId);

            $satkerQuery->where('id', $userSatkerId);
            $flatQuery->where('id', $userSatkerId);
            
            $tableQuery->whereIn('id', $descendantIds);
            $listQuery->whereIn('id', $descendantIds);
            $parentsQuery->whereIn('id', $descendantIds);
        } else {
            $satkerQuery->whereNull('parent_satker_id');
            $flatQuery->whereNull('parent_satker_id');
        }

        $satkers = $satkerQuery->orderBy('kode_satker', 'asc')->get();
        $allSatkersFlat = $flatQuery->orderBy('kode_satker', 'asc')->get();

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
        $periodes = Periode::orderBy('created_at', 'asc')->get();
        $jabatan = Jabatan::with('fungsional')->get();
        $pegawais = User::all();
        $jenis_penugasans = MJenisPenugasan::all();
        $wilayahs = Wilayah::whereIn('tingkat_wilayah_id', [1, 2, 4])->orderBy('kode_wilayah', 'asc')->get();
        $kabupaten = Wilayah::where('tingkat_wilayah_id', 3)->orderBy('kode_wilayah', 'asc')->get();
        $refJabatanSatker = RefJabatanSatker::orderBy('label_jabatan', 'asc')->get();
        $roles = MRole::whereIn('key', ['admin_satker', 'pejabat'])->get();

        return view('admin.satker.index', compact('satkers', 'allSatkers', 'listAllSatkers', 'wilayahs', 'kabupaten', 'parents', 'jenisSatkers', 'jabatan', 'pegawais', 'jenis_penugasans', 'periodes', 'roles', 'userRoles', 'allSatkersFlat', 'refJabatanSatker'));
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


        return redirect()->back()->with('success', 'Satuan Kerja berhasil diperbarui');
    }

    public function destroy($id)
    {
        $satker = Satker::findOrFail($id);
        
        // Cek apakah punya bawahan
        if($satker->children()->count() > 0) {
            // Menggunakan withErrors agar terbaca oleh script Toast Anda
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
        return redirect()->back()->with('success', 'Satuan Kerja berhasil dihapus');
    }

    public function getUsersBySatker($id)
    {
        \App\Models\Penugasan::where('satker_id', $id)
            ->where('status_aktif', 0)
            ->whereNotNull('tanggal_selesai_cuti')
            ->whereNull('tanggal_selesai') // Pastikan bukan yang benar-benar selesai tugas
            ->whereDate('tanggal_selesai_cuti', '<', now()->toDateString())
            ->update([
                'status_aktif'         => 1, // Aktifkan lagi
                'tanggal_mulai_cuti'   => null,
                'tanggal_selesai_cuti' => null
            ]);

        $penugasans = \App\Models\Penugasan::with([
                'user.userDetail', 
                'user.roles',
                'jenisPenugasan'
            ])
            ->where('satker_id', $id)
            ->orderBy('status_aktif', 'desc') 
            ->orderBy('tanggal_mulai', 'desc') 
            ->get()
            ->map(function ($penugasan) {
                $user = $penugasan->user;
                $roles = ($user && $user->roles) ? $user->roles->pluck('nama')->values() : collect();

                // Cek apakah dia sedang cuti (Status 0 + Ada tgl cuti)
                $isCuti = false;
                if ($penugasan->status_aktif == 0 && $penugasan->tanggal_mulai_cuti) {
                    $isCuti = true;
                }

                return [
                    'penugasan_id'      => $penugasan->id,
                    'name'              => $user ? $user->name : 'Tanpa Nama',
                    'nip'               => $user ? $user->nip : '-',
                    'email'             => $user ? $user->email : '-',
                    'jabatan'           => ($user && $user->userDetail) ? $user->userDetail->tampil_jabatan : '-',
                    'roles'             => $roles->isNotEmpty() ? $roles : '-',
                    'jenis_penugasan'   => $penugasan->jenisPenugasan ? $penugasan->jenisPenugasan->nama : '-',
                    'status_aktif'      => $penugasan->status_aktif ?? 0,
                    
                    'is_cuti'           => $isCuti,
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

        // 2. BACA DARI SETUP RUMUS (Dengan Perbaikan Bobot Prioritas Mutlak)
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
            // KUNCI PERBAIKAN: Bobot bertingkat agar Jabatan (100) selalu mengalahkan Wilayah (10)
            ->orderByRaw('(CASE WHEN ref_jabatan_satker_id IS NOT NULL THEN 100 ELSE 0 END) + 
                          (CASE WHEN tingkat_wilayah_id IS NOT NULL THEN 10 ELSE 0 END) + 
                          (CASE WHEN jenis_satker_id IS NOT NULL THEN 1 ELSE 0 END) DESC')
            ->first();

        if (!$setup) {
            return response()->json(['error' => 'Setup Rumus belum dikonfigurasi untuk kombinasi ini. Silakan buat di menu Setup Kode.'], 404);
        }

        $kodeBaru = $setup->pola;
        $parentCode = '';
        $kodeJf = '';

        if (str_contains($kodeBaru, '[PARENT]')) {
            $parent = Satker::find($parentId);
            $parentCode = $parent ? $parent->kode_satker : '';
            $kodeBaru = str_replace('[PARENT]', $parentCode, $kodeBaru);
        }

        if (str_contains($kodeBaru, '[KODE_WILAYAH]')) {
            $kodeBaru = str_replace('[KODE_WILAYAH]', $kodeWilayah, $kodeBaru);
        }

        if (str_contains($kodeBaru, '[KODE_JF]')) {
            $jabatanFungsionalId = $request->jabatan_id; 
            $jf = \App\Models\JabatanFungsional::find($jabatanFungsionalId);
            $kodeJf = $jf ? $jf->kode : '';
            $kodeBaru = str_replace('[KODE_JF]', $kodeJf, $kodeBaru);
        }

        // --- TAMBAHAN FIX KODE JABATAN FIX (Tidak Increment) ---
        if ($refJabatanId) {
            $jabatanSatker = \App\Models\RefJabatanSatker::find($refJabatanId);
            if ($jabatanSatker) {
                // 1. Jika di masa depan Anda memakai tag [KODE_JABATAN] di setup rumus
                if (str_contains($kodeBaru, '[KODE_JABATAN]')) {
                    $kodeBaru = str_replace('[KODE_JABATAN]', $jabatanSatker->kode_dasar ?? '', $kodeBaru);
                }

                // 2. Jika jabatan ini bersifat fix (bukan increment) dan punya kode_dasar,
                // Timpa pola [INC:xx] menjadi kode dasar (contoh: 00).
                if (!$jabatanSatker->is_increment && !empty($jabatanSatker->kode_dasar)) {
                    $kodeBaru = preg_replace('/\[INC:\d+\]/', $jabatanSatker->kode_dasar, $kodeBaru);
                }
            }
        }

        // 4. Logic INC & Pencarian Bolong (Hanya jalan jika ada pola [INC:xx])
        $gaps = []; 
        
        if (preg_match('/\[INC:(\d+)\]/', $kodeBaru, $matches)) {
            $digit = (int)$matches[1];
            $prefixPattern = explode('[INC', $setup->pola)[0];

            if (str_contains($prefixPattern, '[PARENT]')) {
                 $prefixPattern = str_replace('[PARENT]', $parentCode ?? '', $prefixPattern);
            }
            if (str_contains($prefixPattern, '[KODE_WILAYAH]')) {
                 $prefixPattern = str_replace('[KODE_WILAYAH]', $kodeWilayah ?? '', $prefixPattern);
            }
            if (str_contains($prefixPattern, '[KODE_JF]')) {
                 $prefixPattern = str_replace('[KODE_JF]', $kodeJf ?? '', $prefixPattern);
            }

            $query = Satker::where('kode_satker', 'like', $prefixPattern . '%');
            
            if ($parentId) {
                $query->where('parent_satker_id', $parentId);
            } else {
                $query->whereNull('parent_satker_id');
            }

            // Kode double pengecekan periode dihapus, disisakan 1 yang rapi
            $periodeId = $request->periode_id;
            if ($periodeId) {
                $query->where('periode_id', $periodeId);
            }
            
            $existingCodes = $query->pluck('kode_satker')->toArray();

            $existingNums = [];
            $expectedLength = strlen($prefixPattern) + $digit;

            foreach ($existingCodes as $c) {
                $c = trim($c);
                if (strlen($c) === $expectedLength) {
                    $numPart = substr($c, -$digit);
                    if (is_numeric($numPart)) {
                        $existingNums[] = intval($numPart);
                    }
                }
            }
            sort($existingNums);

            $maxNum = !empty($existingNums) ? max($existingNums) : 0;

            if ($request->has('start_num') && is_numeric($request->start_num)) {
                $customStart = (int) $request->start_num - 1;
                $maxNum = max($maxNum, $customStart);
            }

            $loopStart = 1;
            if ($request->has('start_num') && is_numeric($request->start_num)) {
                $loopStart = (int) $request->start_num;
            }

            for ($i = $loopStart; $i < $maxNum; $i++) {
                if (!in_array($i, $existingNums)) {
                    $gaps[] = $prefixPattern . str_pad($i, $digit, '0', STR_PAD_LEFT);
                }
            }

            $nextNum = $maxNum + 1;
            $incStr = str_pad($nextNum, $digit, '0', STR_PAD_LEFT);
            $kodeBaru = str_replace($matches[0], $incStr, $kodeBaru);
        }

        return response()->json([
            'code' => $kodeBaru,
            'gaps' => $gaps,
            'default_nama' => $setup->default_nama_satker ?? ''
        ]);
    }
}