<?php

namespace App\Http\Controllers;

use App\Models\MRole;
use App\Models\MJenisPenugasan;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    private $availableMenus = [
        'dashboard' => 'Dashboard',
        'wilayah' => 'Wilayah',
        'satker' => 'Satuan Kerja',
        'jabatan' => 'Jabatan Fungsional',
        'pegawai' => 'Pegawai',
        'periode' => 'Periode',
        'audit_log' => 'Audit Log',
        'setting_kode' => 'Rumus Kode',
        'manajemen_role' => 'Manajemen Role (Akses Menu)',
        'regulasi' => 'Regulasi Penugasan'
    ];

    public function index()
    {
        $userRoles = auth()->user()->roles->pluck('key')->toArray();
        if (!in_array('super_admin', $userRoles)) abort(403, 'Akses Ditolak.');

        $roles = MRole::where('key', '!=', 'pejabat')->orderBy('id', 'asc')->get();
        $penugasans = MJenisPenugasan::orderBy('id', 'asc')->get();
        $availableMenus = $this->availableMenus;

        return view('admin.role.index', compact('roles', 'penugasans', 'availableMenus'));
    }

    public function store(Request $request)
    {
        $request->validate(['key' => 'required|unique:m_roles,key', 'nama' => 'required|string|max:100']);
        MRole::create([
            'key' => strtolower(str_replace(' ', '_', $request->key)),
            'nama' => $request->nama, 'menus' => [], 'is_assignable' => true
        ]);
        return redirect()->back()->with('success', 'Role baru berhasil ditambahkan!');
    }

    public function destroy($id)
    {
        $role = MRole::findOrFail($id);
        if (in_array($role->key, ['super_admin', 'pejabat'])) {
            return redirect()->back()->with('error', 'Role inti sistem tidak dapat dihapus!');
        }
        $role->delete();
        return redirect()->back()->with('success', 'Role berhasil dihapus!');
    }

    public function storePenugasan(Request $request)
    {
        $request->validate(['nama' => 'required|string|max:100']);
        MJenisPenugasan::create(['nama' => $request->nama, 'menus' => [], 'is_assignable' => true]);
        return redirect()->back()->with('success', 'Jenis Penugasan berhasil ditambahkan!');
    }

    public function destroyPenugasan($id)
    {
        MJenisPenugasan::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Jenis Penugasan berhasil dihapus!');
    }

    public function update(Request $request, $id)
    {
        $type = $request->input('target_type');
        $menusData = $request->input('menus') ?? [];
        $isAssignable = $request->has('is_assignable');

        if ($type === 'penugasan') {
            MJenisPenugasan::findOrFail($id)->update(['menus' => $menusData, 'is_assignable' => $isAssignable]);
        } else {
            MRole::findOrFail($id)->update(['nama' => $request->nama, 'menus' => $menusData, 'is_assignable' => $isAssignable]);
        }

        return redirect()->back()->with('success', 'Hak akses berhasil diperbarui!');
    }
}