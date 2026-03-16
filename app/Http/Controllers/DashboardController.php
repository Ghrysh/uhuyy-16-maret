<?php

namespace App\Http\Controllers;

use App\Models\Satker;
use App\Models\Wilayah;
use App\Models\User;
use App\Models\Penugasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Mengambil Data Statistik untuk Cards
        $stats = [
            'total_satker'  => Satker::count(),
            'total_wilayah' => Wilayah::count(),
            'total_pegawai' => User::count(),
            'penugasan_aktif' => Penugasan::where('status_aktif', true)->count(),
        ];

        // 2. Data untuk Bar Chart (Distribusi Satker per Eselon)
        // Mengasumsikan m_eselon_level memiliki nama I, II, III, IV, V
        $eselonData = DB::table('m_jenis_satker')
            // Join langsung ke tabel satker menggunakan foreign key yang ada di satker
            ->leftJoin('satker', 'm_jenis_satker.id', '=', 'satker.jenis_satker_id')
            // Pilih nama eselon dan hitung jumlah ID satker
            ->select('m_jenis_satker.nama', DB::raw('count(satker.id) as total'))
            // Kelompokkan berdasarkan ID dan nama level
            ->groupBy('m_jenis_satker.id', 'm_jenis_satker.nama')
            // Urutkan berdasarkan ID eselon (I, II, III, dst)
            ->orderBy('m_jenis_satker.id', 'asc')
            ->get();

        // 3. Data untuk Donut Chart (Distribusi Jenis Penugasan)
        $penugasanData = DB::table('m_jenis_penugasan')
            ->leftJoin('penugasan', 'm_jenis_penugasan.id', '=', 'penugasan.jenis_penugasan_id')
            ->select('m_jenis_penugasan.nama', DB::raw('count(penugasan.id) as total'))
            ->groupBy('m_jenis_penugasan.id', 'm_jenis_penugasan.nama')
            ->get();

        // 4. Hitung Satker tanpa pejabat definitif (Alert)
        // Mencari satker yang tidak memiliki penugasan dengan jenis 'Definitif'
        $definitifId = DB::table('m_jenis_penugasan')->where('nama', 'Definitif')->value('id');
        $satkerTanpaDefinitif = Satker::whereDoesntHave('penugasan', function($query) use ($definitifId) {
            $query->where('jenis_penugasan_id', $definitifId)->where('status_aktif', true);
        })->count();

        return view('admin.dashboard', compact('stats', 'eselonData', 'penugasanData', 'satkerTanpaDefinitif'));
    }
}