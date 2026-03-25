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
        // Untuk Pohon Hierarki (Hanya Root)
        $satkers = Satker::with('children')
            ->whereNull('parent_satker_id')
            ->orderBy('kode_satker', 'asc')
            ->get();

        
        $allSatkersFlat = Satker::with(['childrenRecursive', 'wilayah', 'eselon'])
            ->whereNull('parent_satker_id') // 
            ->orderBy('kode_satker', 'asc')
            ->get();


        // Untuk Tabel (Semua Data)
        $search = $request->query('search');

        $allSatkers = Satker::query()
            ->with(['wilayah', 'eselon']) // Eager loading agar ringan
            ->when($search, function ($query, $search) {
                return $query->where('nama_satker', 'like', "%{$search}%")
                            ->orWhere('kode_satker', 'like', "%{$search}%");
            })
            ->orderBy('kode_satker', 'asc')
            ->paginate(10);
        
        $listAllSatkers = Satker::select('id', 'nama_satker', 'kode_satker', 'jenis_satker_id', 'periode_id')
            ->orderBy('kode_satker', 'asc')
            ->get();

        $user = Auth::user();
        $userRoles = $user->roles()->pluck('key')->toArray();

        // dd($userRoles);
        $jenisSatkers = DB::table('m_jenis_satker')->get();
        $periodes = Periode::orderBy('created_at', 'asc')->get();
        $jabatan = Jabatan::with('fungsional')->get();
        $pegawais = User::all();
        $jenis_penugasans = MJenisPenugasan::all();
        $wilayahs = Wilayah::whereIn('tingkat_wilayah_id', [1, 2, 4])
                    ->orderBy('kode_wilayah', 'asc')
                    ->get();
    
        $kabupaten = Wilayah::where('tingkat_wilayah_id', 3)
                    ->orderBy('kode_wilayah', 'asc')
                    ->get();

        // dd($kabupaten);
        $refJabatanSatker = RefJabatanSatker::orderBy('label_jabatan', 'asc')->get();
        // dd($refJabatanSatker);
        $parents = Satker::all();
        $roles = MRole::whereIn('key', ['admin_satker', 'pejabat'])->get();

        // dd($jabatan);

        return view('admin.satker.index', compact('satkers', 'allSatkers', 'listAllSatkers', 'wilayahs', 'kabupaten', 'parents', 'jenisSatkers', 'jabatan', 'pegawais', 'jenis_penugasans', 'periodes', 'roles', 'userRoles', 'allSatkersFlat', 'refJabatanSatker'));
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
        $users = User::whereHas('penugasan', function ($q) use ($id) {
                $q->where('satker_id', $id);
            })
            ->with([
                'userDetail:id,nip_baru,tampil_jabatan',
                'roles:id,nama',
                'penugasan' => function ($q) use ($id) {
                    $q->where('satker_id', $id)
                    ->with('jenisPenugasan:id,nama');
                }
            ])
            ->get()
            ->map(function ($user) {

                $activePenugasan = $user->penugasan->first();
                $roles = $user->roles->pluck('nama')->values();

                return [
                    'penugasan_id'      => $activePenugasan?->id,
                    'name'              => $user->name,
                    'nip'               => $user->nip,
                    'email'             => $user->email,
                    'jabatan'           => optional($user->userDetail)->tampil_jabatan ?? '-',

                    'roles'             => $roles->isNotEmpty() ? $roles : '-',

                    'jenis_penugasan'   => optional($activePenugasan?->jenisPenugasan)->nama ?? '-',

                    'status_aktif'      => $activePenugasan?->status_aktif ?? 0,

                    'tanggal_mulai'     => $activePenugasan?->tanggal_mulai
                                            ? \Carbon\Carbon::parse($activePenugasan->tanggal_mulai)->format('d-m-Y')
                                            : '-',

                    'tanggal_selesai'   => $activePenugasan?->tanggal_selesai
                                            ? \Carbon\Carbon::parse($activePenugasan->tanggal_selesai)->format('d-m-Y')
                                            : '-',
                ];
            });

        return response()->json($users);
    }

    public function generateCode(Request $request)
    {
        $jenisId = $request->jenis_id;
        $parentId = $request->parent_id;
        $refJabatanId = $request->ref_jabatan_satker_id;
        
        // 1. Ambil Tingkat Wilayah (Pusat/PTKN/Prov) berdasarkan wilayah yang dipilih user
        $wilayahId = $request->wilayah_id;
        $tingkatWilayahId = null;
        if ($wilayahId) {
            $wilayah = \App\Models\Wilayah::find($wilayahId);
            if ($wilayah) $tingkatWilayahId = $wilayah->tingkat_wilayah_id;
        }

        // 2. BACA DARI SETUP RUMUS (Bukan lagi dari ref_jabatan_satker)
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
            ->orderByRaw('(CASE WHEN tingkat_wilayah_id IS NOT NULL THEN 1 ELSE 0 END) + 
                          (CASE WHEN jenis_satker_id IS NOT NULL THEN 1 ELSE 0 END) + 
                          (CASE WHEN ref_jabatan_satker_id IS NOT NULL THEN 1 ELSE 0 END) DESC')
            ->first();

        // Jika user belum bikin Setup, beri tahu dengan Error
        if (!$setup) {
            return response()->json(['error' => 'Setup Rumus belum dikonfigurasi untuk kombinasi ini. Silakan buat di menu Setup Kode.'], 404);
        }

        $kodeBaru = $setup->pola;

        if (str_contains($kodeBaru, '[PARENT]')) {
            $parent = Satker::find($parentId);
            $parentCode = $parent ? $parent->kode_satker : '';
            $kodeBaru = str_replace('[PARENT]', $parentCode, $kodeBaru);
        }

        if (str_contains($kodeBaru, '[KODE_WILAYAH]')) {
            $wilayah = \App\Models\Wilayah::find($wilayahId);
            $kodeWilayah = $wilayah ? $wilayah->kode_wilayah : ''; 
            $kodeBaru = str_replace('[KODE_WILAYAH]', $kodeWilayah, $kodeBaru);
        }

        if (str_contains($kodeBaru, '[KODE_JF]')) {
            $jabatanFungsionalId = $request->jabatan_id; 
            $jf = \App\Models\JabatanFungsional::find($jabatanFungsionalId);
            $kodeJf = $jf ? $jf->kode : '';
            $kodeBaru = str_replace('[KODE_JF]', $kodeJf, $kodeBaru);
        }

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

            $lastSibling = Satker::where('parent_satker_id', $parentId)
                ->where('kode_satker', 'like', $prefixPattern . '%')
                ->orderBy('kode_satker', 'desc')
                ->first();

            $nextNum = 1;
            if ($lastSibling) {
                $lastNumStr = substr($lastSibling->kode_satker, -$digit);
                if (is_numeric($lastNumStr)) {
                    $nextNum = intval($lastNumStr) + 1;
                }
            }
            
            $incStr = str_pad($nextNum, $digit, '0', STR_PAD_LEFT);
            $kodeBaru = str_replace($matches[0], $incStr, $kodeBaru);
        }

        return response()->json([
            'code' => $kodeBaru,
            'default_nama' => $setup->default_nama_satker ?? ''
        ]);
    }
}