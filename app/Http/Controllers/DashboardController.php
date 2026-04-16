<?php

namespace App\Http\Controllers;

use App\Models\Satker;
use App\Models\Wilayah;
use App\Models\User;
use App\Models\Penugasan;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $userRoles = $user->roles;
        $isSuperAdmin = $userRoles->contains('key', 'super_admin');
        
        if (!$isSuperAdmin) {
            $allowedMenus = [];
            foreach($userRoles as $role) {
                if ($role->key === 'pejabat') {
                    $activeAssignment = \App\Models\Penugasan::where('user_id', $user->id)->where('status_aktif', 1)->first();
                    if ($activeAssignment && $activeAssignment->jenisPenugasan && is_array($activeAssignment->jenisPenugasan->menus)) {
                        $allowedMenus = array_merge($allowedMenus, $activeAssignment->jenisPenugasan->menus);
                    }
                } else if (is_array($role->menus)) {
                    $allowedMenus = array_merge($allowedMenus, $role->menus);
                }
            }
            
            if (empty($allowedMenus['dashboard'])) {
                if (isset($allowedMenus['satker'])) return redirect()->route('admin.satker.index');
                if (isset($allowedMenus['jabatan'])) return redirect()->route('admin.jabatan.index');
                if (isset($allowedMenus['pegawai'])) return redirect()->route('admin.pegawai.index');
                abort(403, 'Akses Ditolak. Hubungi Administrator.');
            }
        }

        // FILTER PERIODE AKTIF (Agar Data Tidak Bengkak / Dummy)
        $periodes = Periode::orderBy('created_at', 'asc')->get();
        $activePeriodeId = $request->input('periode_id', $periodes->first()->id ?? null);

        // 1. STATISTIK KARTU ATAS (DIFILTER PER PERIODE)
        $stats = [
            'total_satker'  => Satker::where('periode_id', $activePeriodeId)->count(),
            'total_wilayah' => Wilayah::count(),
            'total_pegawai' => User::count(),
            'penugasan_aktif' => Penugasan::where('status_aktif', true)->whereHas('satker', function($q) use ($activePeriodeId) {
                $q->where('periode_id', $activePeriodeId);
            })->count(),
        ];

        // 2. DISTRIBUSI ESELON
        $eselonData = DB::table('m_jenis_satker')
            ->leftJoin('satker', function($join) use ($activePeriodeId) {
                $join->on('m_jenis_satker.id', '=', 'satker.jenis_satker_id')
                     ->where('satker.periode_id', '=', $activePeriodeId);
            })
            ->select('m_jenis_satker.nama', DB::raw('count(satker.id) as total'))
            ->groupBy('m_jenis_satker.id', 'm_jenis_satker.nama')
            ->orderBy('m_jenis_satker.id', 'asc')
            ->get();

        // 3. DISTRIBUSI JENIS PENUGASAN
        $penugasanData = DB::table('m_jenis_penugasan')
            ->leftJoin('penugasan', function($join) {
                $join->on('m_jenis_penugasan.id', '=', 'penugasan.jenis_penugasan_id')
                     ->where('penugasan.status_aktif', true)
                     ->whereNull('penugasan.deleted_at');
            })
            ->leftJoin('satker', function($join) use ($activePeriodeId) {
                $join->on('penugasan.satker_id', '=', 'satker.id')
                     ->where('satker.periode_id', '=', $activePeriodeId);
            })
            ->select('m_jenis_penugasan.nama', DB::raw('COUNT(satker.id) as total'))
            ->groupBy('m_jenis_penugasan.id', 'm_jenis_penugasan.nama')
            ->get();

        // 4. DAFTAR SATKER KOSONG (Benar-benar tidak ada pejabat aktif sama sekali)
        $satkerKosong = Satker::with(['eselon', 'wilayah'])->where('periode_id', $activePeriodeId)
            ->whereDoesntHave('penugasan', function($query) {
                $query->where('status_aktif', true);
            })->orderBy('kode_satker', 'asc')->get();
        
        $satkerTanpaDefinitif = $satkerKosong->count();

        // 5. KALKULASI KLASIFIKASI LAMA JABATAN & PENSIUN
        $penugasans = Penugasan::with(['user.userDetail'])
            ->where('status_aktif', 1)
            ->whereHas('satker', function($q) use ($activePeriodeId) {
                $q->where('periode_id', $activePeriodeId);
            })->get();

        $klasifikasiLama = [
            '< 6 bln' => 0, '6 bln - 1 thn' => 0, '> 1 - 1.5 thn' => 0, '> 1.5 - 2 thn' => 0,
            '> 2 - 2.5 thn' => 0, '> 2.5 - 3 thn' => 0, '> 3 - 3.5 thn' => 0, '> 3.5 - 4 thn' => 0,
            '> 4 - 4.5 thn' => 0, '> 4.5 - 5 thn' => 0, '> 5 thn' => 0,
        ];

        $klasifikasiPensiun = [
            '< 6 bln' => 0, '< 1 thn' => 0, '< 1.5 thn' => 0, '< 2 thn' => 0, '> 2 thn' => 0, 'Sudah Pensiun' => 0,
        ];

        $now = Carbon::now();

        foreach ($penugasans as $tugas) {
            // Hitung Lama Menjabat
            if ($tugas->tanggal_mulai) {
                $tmt = Carbon::parse($tugas->tanggal_mulai);
                $diff = $tmt->diff($now);
                $bln = ($diff->y * 12) + $diff->m;

                if ($bln < 6) $klasifikasiLama['< 6 bln']++;
                elseif ($bln <= 12) $klasifikasiLama['6 bln - 1 thn']++;
                elseif ($bln <= 18) $klasifikasiLama['> 1 - 1.5 thn']++;
                elseif ($bln <= 24) $klasifikasiLama['> 1.5 - 2 thn']++;
                elseif ($bln <= 30) $klasifikasiLama['> 2 - 2.5 thn']++;
                elseif ($bln <= 36) $klasifikasiLama['> 2.5 - 3 thn']++;
                elseif ($bln <= 42) $klasifikasiLama['> 3 - 3.5 thn']++;
                elseif ($bln <= 48) $klasifikasiLama['> 3.5 - 4 thn']++;
                elseif ($bln <= 54) $klasifikasiLama['> 4 - 4.5 thn']++;
                elseif ($bln <= 60) $klasifikasiLama['> 4.5 - 5 thn']++;
                else $klasifikasiLama['> 5 thn']++;
            }

            // Hitung Sisa Pensiun
            $user = $tugas->user;
            if ($user && $user->userDetail && $user->userDetail->tmt_pensiun) {
                $tmtPensiun = Carbon::parse($user->userDetail->tmt_pensiun);
                if ($tmtPensiun->isPast()) {
                    $klasifikasiPensiun['Sudah Pensiun']++;
                } else {
                    $diff = $now->diff($tmtPensiun);
                    $bln = ($diff->y * 12) + $diff->m;
                    if ($bln < 6) $klasifikasiPensiun['< 6 bln']++;
                    elseif ($bln < 12) $klasifikasiPensiun['< 1 thn']++;
                    elseif ($bln < 18) $klasifikasiPensiun['< 1.5 thn']++;
                    elseif ($bln < 24) $klasifikasiPensiun['< 2 thn']++;
                    else $klasifikasiPensiun['> 2 thn']++;
                }
            }
        }

        return view('admin.dashboard', compact(
            'stats', 'eselonData', 'penugasanData', 'satkerTanpaDefinitif', 
            'satkerKosong', 'periodes', 'activePeriodeId', 'klasifikasiLama', 'klasifikasiPensiun'
        ));
    }

    // ==============================================================
    // FUNGSI UNDUH LAPORAN EXCEL (TERPISAH PEJABAT & ADMIN)
    // ==============================================================
    public function exportLaporan(Request $request)
    {
        $periodeId = $request->query('periode_id');
        $type = $request->query('type', 'pejabat'); // Ambil parameter tipe (pejabat/admin)

        $query = Satker::with(['penugasan' => function($q) use ($type) {
            $q->where('status_aktif', 1)->with(['user.userDetail', 'user.roles', 'jenisPenugasan']);
            
            // LOGIKA PEMISAHAN: Filter penugasan berdasarkan tipe laporan
            if ($type === 'pejabat') {
                // Hanya tarik yang punya jenis penugasan (Definitif, Plt, Plh)
                $q->whereNotNull('jenis_penugasan_id');
            } elseif ($type === 'admin') {
                // Hanya tarik yang tidak punya jenis penugasan (Role Admin)
                $q->whereNull('jenis_penugasan_id');
            }
        }]);

        if ($periodeId) {
            $query->where('periode_id', $periodeId);
        }

        $satkers = $query->orderBy('kode_satker', 'asc')->get();

        $tipeLabel = $type === 'pejabat' ? 'Pejabat' : 'Admin';
        $fileName = "Laporan_Kekosongan_{$tipeLabel}_" . date('Ymd_His') . ".xls";
        
        $headers = array(
            "Content-type"        => "application/vnd.ms-excel",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $callback = function() use($satkers, $type, $tipeLabel) {
            echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
            echo '<head>';
            echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
            echo '';
            
            echo '<style>
                    th { background-color: #112D4E; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #000000; white-space: nowrap; padding: 10px; font-size: 11pt; }
                    td { border: 1px solid #000000; padding: 5px; vertical-align: middle; font-size: 10pt; }
                    .text-center { text-align: center; }
                    .kosong { background-color: #FEE2E2; color: #991B1B; font-weight: bold; text-align: center; }
                    .terisi { background-color: #D1FAE5; color: #065F46; font-weight: bold; text-align: center; }
                    .nip-text { mso-number-format:"\@"; text-align: center; font-weight: bold; }
                  </style>';
            echo '</head>';
            echo '<body>';
            echo '<table>';

            echo '<thead><tr>';
            // Judul kolom otomatis menyesuaikan jenis laporan
            $kolomStatus = $type === 'pejabat' ? 'Jenis Penugasan' : 'Role Sistem';
            $columns = [
                'Kode Satker', 'Nama Satuan Kerja', 'Status Kursi', $kolomStatus, 
                "Nama {$tipeLabel}", 'NIP', 'TMT Menjabat', 'Lama Menjabat (Thn & Bln)', 'Klasifikasi Lama Menjabat',
                'TMT Pensiun', 'Sisa Waktu Pensiun (Thn & Bln)', 'Klasifikasi Masa Pensiun'
            ];
            foreach ($columns as $col) {
                echo '<th>' . $col . '</th>';
            }
            echo '</tr></thead>';

            echo '<tbody>';
            foreach ($satkers as $satker) {
                
                // JIKA SATKER KOSONG (berdasarkan tipe yang sedang diunduh)
                if ($satker->penugasan->isEmpty()) {
                    echo '<tr>';
                    echo "<td style='mso-number-format:\"\\@\";'>{$satker->kode_satker}</td>";
                    echo "<td>{$satker->nama_satker}</td>";
                    echo "<td class='kosong'>KOSONG</td>";
                    for($i=0; $i<9; $i++) echo "<td class='text-center'>-</td>";
                    echo '</tr>';
                    continue;
                }

                $jumlahPejabat = $satker->penugasan->count();
                $isBarisPertama = true;

                foreach ($satker->penugasan as $tugas) {
                    $user = $tugas->user;
                    $nama = $user ? $user->name : '-';
                    $nip = $user ? $user->nip : '-';
                    
                    $jenisStatus = '-';
                    if (!empty($tugas->jenis_penugasan_id) && $tugas->jenisPenugasan) {
                        $jenisStatus = $tugas->jenisPenugasan->nama;
                    } else {
                        if ($user && $user->roles) {
                            $adminRoles = $user->roles->where('key', '!=', 'pejabat')->pluck('nama')->toArray();
                            $jenisStatus = !empty($adminRoles) ? implode(', ', $adminRoles) : 'Admin / Sistem';
                        }
                    }
                    
                    $tmtJabatan = $tugas->tanggal_mulai ? \Carbon\Carbon::parse($tugas->tanggal_mulai) : null;
                    $strLama = '-';
                    $klasifikasiLama = '-';
                    
                    if ($tmtJabatan) {
                        $diffLama = $tmtJabatan->diff(\Carbon\Carbon::now());
                        $strLama = "{$diffLama->y} Tahun, {$diffLama->m} Bulan";
                        $totalBulanLama = ($diffLama->y * 12) + $diffLama->m;

                        if ($totalBulanLama < 6) $klasifikasiLama = '< 6 bln';
                        elseif ($totalBulanLama <= 12) $klasifikasiLama = '6 bln s.d. 1 tahun';
                        elseif ($totalBulanLama <= 18) $klasifikasiLama = '> 1 tahun s.d. 1 tahun 6 bln';
                        elseif ($totalBulanLama <= 24) $klasifikasiLama = '> 1 tahun 6 bln s.d. 2 tahun';
                        elseif ($totalBulanLama <= 30) $klasifikasiLama = '> 2 tahun s.d. 2 tahun 6 bln';
                        elseif ($totalBulanLama <= 36) $klasifikasiLama = '> 2 tahun 6 bln s.d. 3 tahun';
                        elseif ($totalBulanLama <= 42) $klasifikasiLama = '> 3 tahun s.d. 3 tahun 6 bln';
                        elseif ($totalBulanLama <= 48) $klasifikasiLama = '> 3 tahun 6 bln s.d. 4 tahun';
                        elseif ($totalBulanLama <= 54) $klasifikasiLama = '> 4 tahun s.d. 4 tahun 6 bln';
                        elseif ($totalBulanLama <= 60) $klasifikasiLama = '> 4 tahun 6 bln s.d. 5 tahun';
                        else $klasifikasiLama = '> 5 tahun';
                    }

                    $tmtPensiun = ($user && $user->userDetail && $user->userDetail->tmt_pensiun) ? \Carbon\Carbon::parse($user->userDetail->tmt_pensiun) : null;
                    $strPensiun = '-';
                    $klasifikasiPensiun = '-';

                    if ($tmtPensiun) {
                        if ($tmtPensiun->isPast()) {
                            $strPensiun = "Sudah Pensiun";
                            $klasifikasiPensiun = 'Sudah Pensiun';
                        } else {
                            $diffPensiun = \Carbon\Carbon::now()->diff($tmtPensiun);
                            $strPensiun = "{$diffPensiun->y} Tahun, {$diffPensiun->m} Bulan";
                            $totalBulanPensiun = ($diffPensiun->y * 12) + $diffPensiun->m;

                            if ($totalBulanPensiun < 6) $klasifikasiPensiun = '< 6 bulan pensiun';
                            elseif ($totalBulanPensiun < 12) $klasifikasiPensiun = '< 1 tahun pensiun';
                            elseif ($totalBulanPensiun < 18) $klasifikasiPensiun = '< 1 tahun 6 bulan pensiun';
                            elseif ($totalBulanPensiun < 24) $klasifikasiPensiun = '< 2 tahun pensiun';
                            else $klasifikasiPensiun = '> 2 tahun pensiun';
                        }
                    }

                    echo '<tr>';
                    
                    if ($isBarisPertama) {
                        $rowspanAttr = $jumlahPejabat > 1 ? " rowspan='{$jumlahPejabat}'" : "";
                        echo "<td style='mso-number-format:\"\\@\";'{$rowspanAttr}>{$satker->kode_satker}</td>"; 
                        echo "<td{$rowspanAttr}>{$satker->nama_satker}</td>";
                        echo "<td class='terisi'{$rowspanAttr}>TERISI</td>";
                        $isBarisPertama = false; 
                    }

                    echo "<td class='text-center'>{$jenisStatus}</td>";
                    echo "<td>{$nama}</td>";
                    echo "<td class='nip-text'>{$nip}</td>"; 
                    echo "<td class='text-center'>" . ($tmtJabatan ? $tmtJabatan->format('Y-m-d') : '-') . "</td>";
                    echo "<td>{$strLama}</td>";
                    echo "<td>{$klasifikasiLama}</td>";
                    echo "<td class='text-center'>" . ($tmtPensiun ? $tmtPensiun->format('Y-m-d') : '-') . "</td>";
                    echo "<td>{$strPensiun}</td>";
                    echo "<td>{$klasifikasiPensiun}</td>";
                    echo '</tr>';
                }
            }
            
            echo '</tbody>';
            echo '</table>';
            echo '</body></html>';
        };

        return response()->stream($callback, 200, $headers);
    }
}