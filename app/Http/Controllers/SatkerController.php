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
            'kode_satker_full' => 'required|unique:satker,kode_satker', // Cek unik ke kolom kode_satker
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
            // Kode satker unik kecuali untuk ID satker ini sendiri
            'kode_satker'      => 'required|unique:satker,kode_satker,' . $id,
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

        $nomorParentId = '';
        $middleCode = '';

        // =========================
        // AMBIL KODE PARENT
        // =========================
        if ($parentId) {
            $parent = Satker::find($parentId);
            if ($parent) {
                $nomorParentId = $parent->kode_satker;
            }
        }

        // =========================
        // CEK REF JABATAN SATKER
        // =========================
        if ($refJabatanId) {

            $ref = RefJabatanSatker::find($refJabatanId);

            if ($ref) {

                $baseCode = $ref->kode_dasar;

                // ambil satker terakhir dengan jabatan sama
                $last = Satker::where('parent_satker_id', $parentId)
                    ->where('ref_jabatan_satker_id', $refJabatanId)
                    ->orderBy('kode_satker', 'desc')
                    ->first();

                if ($last) {

                    // ambil 2 digit terakhir
                    $lastNumber = substr($last->kode_satker, -2);

                    $next = intval($lastNumber) + 1;

                    $middleCode = str_pad($next, 2, '0', STR_PAD_LEFT);

                } else {

                    // gunakan kode dasar
                    $middleCode = $baseCode;
                }
            }
        }

        // =========================
        // FALLBACK JIKA TIDAK ADA
        // =========================
        if (!$middleCode) {

            $lastChild = Satker::where('parent_satker_id', $parentId)
                ->orderBy('kode_satker', 'desc')
                ->first();

            $nextNumber = 1;

            if ($lastChild) {

                $lastTwo = substr($lastChild->kode_satker, -2);

                $nextNumber = is_numeric($lastTwo) ? intval($lastTwo) + 1 : 1;
            }

            $middleCode = str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
        }

        // =========================
        // FINAL CODE
        // =========================
        $finalCode = $nomorParentId . $middleCode;

        return response()->json([
            'code' => $finalCode
        ]);
    }
}