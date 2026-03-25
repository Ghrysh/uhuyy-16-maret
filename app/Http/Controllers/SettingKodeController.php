<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Satker;
use App\Models\MJenisSatker;
use App\Models\RefJabatanSatker;
use App\Models\MTingkatWilayah;
use Illuminate\Support\Facades\DB;

class SettingKodeController extends Controller
{
    public function index(Request $request)
    {
        $rumusList = DB::table('rumus_kodes')->get();
        $jenisSatkers = MJenisSatker::all();
        $refJabatans = RefJabatanSatker::all();
        $tingkatWilayahs = MTingkatWilayah::all();

        $satkerRoots = Satker::with(['children', 'eselon'])
            ->whereNull('parent_satker_id')
            ->orderBy('kode_satker', 'asc')
            ->get();

        return view('admin.setting-kode.index', compact('rumusList', 'jenisSatkers', 'refJabatans', 'tingkatWilayahs', 'satkerRoots'));
    }

    public function storeRumus(Request $request)
    {
        $request->validate(['nama_rumus' => 'required|string']);

        $is_auto = $request->is_auto_number == '1';
        $awalan = $request->kode_awalan ?? '';
        $digit = $request->digit_auto_number ?? 2;

        $pola = $is_auto ? "[PARENT]{$awalan}[INC:{$digit}]" : "[PARENT]{$awalan}";

        DB::table('rumus_kodes')->insert([
            'nama_rumus' => $request->nama_rumus,
            'tingkat_wilayah_id' => $request->tingkat_wilayah_id,
            'jenis_satker_id' => $request->jenis_satker_id,
            'ref_jabatan_satker_id' => $request->ref_jabatan_satker_id,
            'kode_awalan' => $awalan,
            'is_auto_number' => $is_auto,
            'digit_auto_number' => $is_auto ? $digit : null,
            'default_nama_satker' => $request->default_nama_satker,
            'pola' => $pola,
            'is_applied' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with(['success' => 'Setup Rumus berhasil disimpan!', 'tab' => $is_auto ? 'auto_number' : 'fix_code']);
    }

    public function updateRumus(Request $request, $id)
    {
        $request->validate(['nama_rumus' => 'required|string']);

        $is_auto = $request->is_auto_number == '1';
        $awalan = $request->kode_awalan ?? '';
        $digit = $request->digit_auto_number ?? 2;
        $pola = $is_auto ? "[PARENT]{$awalan}[INC:{$digit}]" : "[PARENT]{$awalan}";

        // PERBAIKAN FATAL BUG: Kita buang update kriteria (Wilayah/Jabatan) dari Edit!
        // Ini mencegah ID Jabatan hilang menjadi NULL akibat form submission.
        DB::table('rumus_kodes')->where('id', $id)->update([
            'nama_rumus' => $request->nama_rumus,
            'kode_awalan' => $awalan,
            'is_auto_number' => $is_auto,
            'digit_auto_number' => $is_auto ? $digit : null,
            'default_nama_satker' => $request->default_nama_satker,
            'pola' => $pola,
            'is_applied' => false,
            'updated_at' => now(),
        ]);

        return back()->with(['success' => 'Setup Rumus berhasil diperbarui!', 'tab' => $is_auto ? 'auto_number' : 'fix_code']);
    }

    public function destroyRumus($id)
    {
        DB::table('rumus_kodes')->where('id', $id)->delete();
        return back()->with('success', 'Rumus berhasil dihapus!');
    }

    public function applyRumus($id)
    {
        $rumus = DB::table('rumus_kodes')->where('id', $id)->first();
        if(!$rumus) return back()->with('error', 'Rumus tidak ditemukan!');

        DB::table('rumus_kodes')
            ->where(function($q) use ($rumus) {
                if ($rumus->tingkat_wilayah_id) $q->where('tingkat_wilayah_id', $rumus->tingkat_wilayah_id);
                else $q->whereNull('tingkat_wilayah_id');
            })
            ->where(function($q) use ($rumus) {
                if ($rumus->jenis_satker_id) $q->where('jenis_satker_id', $rumus->jenis_satker_id);
                else $q->whereNull('jenis_satker_id');
            })
            ->where(function($q) use ($rumus) {
                if ($rumus->ref_jabatan_satker_id) $q->where('ref_jabatan_satker_id', $rumus->ref_jabatan_satker_id);
                else $q->whereNull('ref_jabatan_satker_id');
            })
            ->update(['is_applied' => false]);

        DB::table('rumus_kodes')->where('id', $id)->update(['is_applied' => true]);

        return back()->with('success', "Rumus berhasil diaktifkan! Mulai sekarang, sistem akan menggunakan pola ini untuk men-generate kode Satker baru.");
    }

    private function cascadeUpdateKode($parentId, $kodeLama, $kodeBaru)
    {
        $children = DB::table('satker')->where('parent_satker_id', $parentId)->get();
        foreach ($children as $child) {
            $childKodeLama = $child->kode_satker;
            if (str_starts_with($childKodeLama, $kodeLama)) {
                $childKodeBaru = $kodeBaru . substr($childKodeLama, strlen($kodeLama));
                DB::table('satker')->where('id', $child->id)->update(['kode_satker' => $childKodeBaru]);
                $this->cascadeUpdateKode($child->id, $childKodeLama, $childKodeBaru);
            }
        }
    }

    public function updateManual(Request $request, $id)
    {
        $request->validate(['kode_satker_baru' => 'required|string|unique:satker,kode_satker,'.$id]);
        $satker = Satker::findOrFail($id);
        $satker->kode_satker = $request->kode_satker_baru;
        $satker->save();
        return back()->with(['success' => 'Kode Satker berhasil diubah secara manual!', 'tab' => 'manual']);
    }
}