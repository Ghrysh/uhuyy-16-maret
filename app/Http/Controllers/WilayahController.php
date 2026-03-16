<?php

namespace App\Http\Controllers;

use App\Models\Wilayah;
use App\Models\MTingkatWilayah;
use App\Models\LogSistem;
use Illuminate\Http\Request;

class WilayahController extends Controller
{
    public function index()
    {
        
        $wilayahs = Wilayah::with('tingkat')
                ->orderByRaw("LPAD(kode_wilayah, 10, '0') ASC")
                ->paginate(10);

        $tingkats = \DB::table('m_tingkat_wilayah')->get(); 
        $parents = Wilayah::whereIn('tingkat_wilayah_id', [1, 2])->get(); 
        
        return view('admin.wilayah.index', compact('wilayahs', 'tingkats', 'parents'));
    }

    public function create()
    {
        $tingkats = MTingkatWilayah::all();
        $parents = Wilayah::whereIn('tingkat_wilayah_id', [1, 2])->get(); // Contoh: hanya Pusat/Provinsi yang bisa jadi parent
        return view('admin.wilayah.create', compact('tingkats', 'parents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_wilayah' => 'required|unique:wilayah,kode_wilayah',
            'nama_wilayah' => 'required',
            'tingkat_wilayah_id' => 'required',
            'parent_wilayah_id' => 'nullable|exists:wilayah,id'
        ]);

        // Wilayah::create($request->all());
        $wilayah = Wilayah::create($request->all());
        LogSistem::create([
            'aksi' => 'CREATE',
            'nama_tabel' => 'wilayah',
            'data_id' => $wilayah->id,
            'perubahan' => 'Menambahkan wilayah: ' . $wilayah->nama_wilayah,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('admin.wilayah.index')->with('success', 'Wilayah berhasil ditambahkan');
    }

    /**
     * Fungsi Update untuk menangani perubahan data dari Modal Edit
     */
    public function update(Request $request, $id)
    {
        $wilayah = Wilayah::findOrFail($id);

        $request->validate([
            // Abaikan unique check untuk ID wilayah ini sendiri
            'kode_wilayah' => 'required|unique:wilayah,kode_wilayah,' . $id,
            'nama_wilayah' => 'required',
            'tingkat_wilayah_id' => 'required',
            'parent_wilayah_id' => 'nullable|exists:wilayah,id'
        ]);

        $wilayah->update($request->all());

        LogSistem::create([
            'aksi' => 'UPDATE',
            'nama_tabel' => 'wilayah',
            'data_id' => $wilayah->id,
            'perubahan' => 'Memperbarui wilayah: ' . $wilayah->nama_wilayah,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('admin.wilayah.index')->with('success', 'Data wilayah berhasil diperbarui');
    }

    public function destroy($id)
    {
        $wilayah = Wilayah::findOrFail($id);
        $wilayah->delete();

        LogSistem::create([
            'aksi' => 'DELETE',
            'nama_tabel' => 'wilayah',
            'data_id' => $wilayah->id,
            'perubahan' => 'Menghapus wilayah: ' . $wilayah->nama_wilayah,
            'user_id' => auth()->id(),
        ]);
        
        return redirect()->route('admin.wilayah.index')->with('success', 'Wilayah berhasil dihapus');
    }
}