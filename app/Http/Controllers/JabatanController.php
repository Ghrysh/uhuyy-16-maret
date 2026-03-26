<?php
namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\MJenisJabatan;
use App\Models\MJenisSatker;
use App\Models\LogSistem;
use App\Models\JabatanFungsional;
use App\Models\Satker;
use App\Models\DistribusiKuota;
use Illuminate\Http\Request;

class JabatanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sortField = $request->input('sort', 'kode_jabatan');
        $sortDirection = $request->input('direction', 'asc');

        $jabatans = Jabatan::with(['jenis', 'jenisSatker', 'fungsional'])
            ->when($search, function ($query, $search) {
                return $query->where('nama_jabatan', 'like', "%{$search}%")
                            ->orWhere('kode_jabatan', 'like', "%{$search}%");
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate(10)
            ->withQueryString();

        $jenis_jabatans = MJenisJabatan::all();
        $eselons = MJenisSatker::all();
        $fungsionals = \App\Models\JabatanFungsional::orderBy('name', 'asc')->get();
        $idFungsional = $jenis_jabatans->where('nama', 'Fungsional')->first()->id ?? '';

        $lastJabatan = Jabatan::selectRaw('MAX(SUBSTRING(kode_jabatan, 1, 3)) as base_last')
                            ->first();
        
        $nextBaseCode = $lastJabatan->base_last ? (int)$lastJabatan->base_last + 1 : 801;

        $dropdownJabatans = Jabatan::with('fungsional')->orderBy('kode_jabatan', 'asc')->get();

        if ($request->ajax()) {
            return view('admin.jabatan.index', compact('jabatans', 'jenis_jabatans', 'eselons', 'fungsionals', 'idFungsional', 'nextBaseCode', 'dropdownJabatans'))->render();
        }

        return view('admin.jabatan.index', compact('jabatans', 'jenis_jabatans', 'eselons', 'fungsionals', 'idFungsional', 'nextBaseCode', 'dropdownJabatans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_jabatan'          => 'required|unique:jabatan,kode_jabatan',
            'nama_jabatan'          => 'required',
            'jenis_jabatan_id'      => 'required|exists:m_jenis_jabatan,id',
            'jenis_satker_id'       => 'nullable|exists:m_jenis_satker,id',
            'jabatan_fungsional_id' => 'nullable|exists:jabatan_fungsionals,id', // Tambahkan ini
        ]);

        try {
            // Menggunakan $request->all() sudah aman karena jabatan_fungsional_id 
            // sudah masuk ke $fillable di Model Jabatan
            $jabatan = Jabatan::create($request->all());

            LogSistem::create([
                'aksi'       => 'CREATE',
                'nama_tabel' => 'jabatan',
                'data_id'    => $jabatan->id, 
                'perubahan'  => 'Menambahkan jabatan baru: ' . $jabatan->nama_jabatan . ' (Kode: ' . $jabatan->kode_jabatan . ')',
                'user_id'    => auth()->id(),
            ]);

            return redirect()->back()->with('success', 'Jabatan berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambah data: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_jabatan'          => 'required',
            'jenis_jabatan_id'      => 'required|exists:m_jenis_jabatan,id',
            'jenis_satker_id'       => 'nullable|exists:m_jenis_satker,id',
            'jabatan_fungsional_id' => 'nullable|exists:jabatan_fungsionals,id', // Tambahkan ini
        ]);

        try {
            $jabatan = Jabatan::findOrFail($id);
            
            // Update semua field termasuk jabatan_fungsional_id
            $jabatan->update($request->all());

            LogSistem::create([
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

    public function destroy($id)
    {
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
        
        $satkers = Satker::orderBy('kode_satker', 'asc')->get();

        $kuotas = DistribusiKuota::where('jabatan_id', $jabatan_id)
                    ->get()
                    ->keyBy('satker_id');
        
        $data = $satkers->map(function($satker) use ($kuotas) {
            $kuota = $kuotas->get($satker->id);
            $level = $satker->parent_satker_id ? 1 : 0; 
            
            return [
                'id'            => $satker->id,
                'nama_satker'   => $satker->nama_satker,
                'level'         => $level,
                'kuota_pertama' => $kuota ? $kuota->kuota_pertama : 0,
                'kuota_muda'    => $kuota ? $kuota->kuota_muda : 0,
                'kuota_madya'   => $kuota ? $kuota->kuota_madya : 0,
                'kuota_utama'   => $kuota ? $kuota->kuota_utama : 0,
            ];
        });

        return response()->json($data);
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