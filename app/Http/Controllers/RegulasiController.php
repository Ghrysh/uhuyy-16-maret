<?php

namespace App\Http\Controllers;

use App\Models\MRole;
use App\Models\MJenisPenugasan;
use Illuminate\Http\Request;

class RegulasiController extends Controller
{
    public function index()
    {
        // Hanya yang is_assignable = true yang dikelola regulasinya
        $roles = MRole::whereNotIn('key', ['pejabat', 'super_admin'])->where('is_assignable', true)->orderBy('id', 'asc')->get();
        $penugasans = MJenisPenugasan::where('is_assignable', true)->orderBy('id', 'asc')->get();

        return view('admin.regulasi.index', compact('roles', 'penugasans'));
    }

    public function update(Request $request, $id)
    {
        $type = $request->input('target_type'); // 'role' atau 'penugasan'
        $regulationsData = $request->input('regulations') ?? [];

        if ($type === 'penugasan') {
            $item = MJenisPenugasan::findOrFail($id);
            $item->update(['regulations' => $regulationsData]);
        } else {
            $item = MRole::findOrFail($id);
            $item->update(['regulations' => $regulationsData]);
        }

        return redirect()->back()->with('success', 'Regulasi Penugasan berhasil disimpan!');
    }
}