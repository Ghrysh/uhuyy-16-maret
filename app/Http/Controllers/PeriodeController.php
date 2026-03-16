<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Periode;
use App\Models\Satker;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PeriodeController extends Controller
{
    public function index()
    {
        $periodes = Periode::latest()->paginate(10);
        return view('admin.periode.index', compact('periodes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_periode' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        Periode::create([
            'nama_periode' => $request->nama_periode,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->back()->with('success', 'Periode berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_periode' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $periode = Periode::findOrFail($id);
        $periode->update($request->only(['nama_periode', 'keterangan']));

        return redirect()->back()->with('success', 'Periode berhasil diperbarui');
    }

    public function destroy($id)
    {
        $periode = Periode::findOrFail($id);

        // Cek apakah periode masih dipakai di tabel satker
        $isUsed = Satker::where('periode_id', $periode->id)->exists();

        if ($isUsed) {
            return redirect()->back()
                ->with('error', 'Periode tidak bisa dihapus karena masih digunakan oleh data Satker.');
        }

        $periode->delete();

        return redirect()->back()->with('success', 'Periode berhasil dihapus');
    }
}