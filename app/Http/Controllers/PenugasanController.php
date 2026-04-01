<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Penugasan;
use App\Models\MJenisPenugasan;
use App\Models\User;
use App\Models\Satker;
use App\Models\Jabatan;
use App\Models\LogSistem;
use App\Http\Controllers\getProfileController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PenugasanController extends Controller
{
    public function index()
    {
        $penugasans = Penugasan::with(['user', 'satker', 'jabatan', 'jenisPenugasan'])
            ->latest()
            ->get();

        $pegawais = User::all();
        $satkers = Satker::all();
        $jabatans = Jabatan::all();
        $jenis_penugasans = MJenisPenugasan::all();

        return view('admin.penugasan.index', compact('penugasans', 'pegawais', 'satkers', 'jabatans', 'jenis_penugasans'));
    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'user_nip' => 'required|string',
    //         'satker_id' => 'required|exists:satker,id',
    //         'satker_kode' => 'required|exists:satker,kode_satker',
    //         'jabatan_id' => 'nullable|exists:jabatan,id',
    //         'jenis_penugasan_id' => 'nullable|exists:m_jenis_penugasan,id',
    //         'tanggal_mulai' => 'required|date',
    //         'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
    //         'status_aktif' => 'nullable|boolean',
    //         'role_id' => 'required|exists:m_roles,id',
    //     ]);

    //     return DB::transaction(function () use ($request) {
    //         try {
    //             $user = User::where('nip', $request->user_nip)->first();

    //             if (!$user) {
    //                 $response = Http::timeout(30)
    //                     ->acceptJson()
    //                     ->get('https://ropegdev.kemenag.go.id/simsdm/pegawai/search', [
    //                         'nip' => $request->user_nip
    //                     ]);

    //                 if (!$response->successful()) {
    //                     return redirect()->back()->with('error', 'Gagal mengambil data pegawai dari server ROPEG.');
    //                 }

    //                 $responseData = $response->json();

    //                 Log::info("Raw Data API ROPEG NIP: " . $request->user_nip, [
    //                     'res' => $responseData
    //                 ]);


    //                 if (!isset($responseData['success']) || !$responseData['success']) {
    //                     return redirect()->back()->with('error', 'Gagal mengambil data pegawai dari API.');
    //                 }

    //                 // Ambil data dengan fallback ke array kosong agar tidak error object
    //                 $dataApi = $responseData['data']['data'] ?? [];
                    
    //                 // Jika data kosong, gagalkan
    //                 if (empty($dataApi)) {
    //                     return redirect()->back()->with('error', 'Data NIP tersebut tidak ditemukan di server Kemenag.');
    //                 }

    //                 // Gunakan helper data_get agar lebih aman mengambil nested array/object
    //                 $nipFinal = data_get($dataApi, 'NIP') ?? data_get($dataApi, 'NIP_BARU') ?? $request->user_nip;
    //                 $namaFinal = data_get($dataApi, 'NAMA_LENGKAP') ?? data_get($dataApi, 'NAMA') ?? 'Tanpa Nama';

    //                 $user = User::create([
    //                     'nip'   => data_get($dataApi, 'NIP_BARU'),
    //                     'name'  => $namaFinal,
    //                     'email' => data_get($dataApi, 'EMAIL_DINAS') ?? data_get($dataApi, 'EMAIL') ?? ($nipFinal . '@kemenag.go.id'),
    //                     'password' => bcrypt('12345678'),
    //                     'satker_id' => $request->satker_kode,
    //                 ]);

    //                 $detailData = [
    //                     'nip'               => $nipFinal,
    //                     'nip_baru'          => data_get($dataApi, 'NIP_BARU'),
    //                     'nama'              => data_get($dataApi, 'NAMA'),
    //                     'nama_lengkap'      => data_get($dataApi, 'NAMA_LENGKAP'),
    //                     'agama'             => data_get($dataApi, 'AGAMA'),
    //                     'tempat_lahir'      => data_get($dataApi, 'TEMPAT_LAHIR'),
    //                     'tanggal_lahir'     => data_get($dataApi, 'TANGGAL_LAHIR'),
    //                     'jenis_kelamin'     => data_get($dataApi, 'JENIS_KELAMIN'),
    //                     'pendidikan'        => data_get($dataApi, 'PENDIDIKAN'),
    //                     'jenjang_pendidikan' => data_get($dataApi, 'JENJANG_PENDIDIKAN'),
    //                     'kode_level_jabatan' => data_get($dataApi, 'KODE_LEVEL_JABATAN'),
    //                     'level_jabatan'     => data_get($dataApi, 'LEVEL_JABATAN'),
    //                     'pangkat'           => data_get($dataApi, 'PANGKAT'),
    //                     'gol_ruang'         => data_get($dataApi, 'GOL_RUANG'),
    //                     'tmt_cpns'          => $this->normalizeDate(data_get($dataApi, 'TMT_CPNS')),
    //                     'tmt_pangkat'       => $this->normalizeDate(data_get($dataApi, 'TMT_PANGKAT')),
    //                     'mk_tahun'          => data_get($dataApi, 'MK_TAHUN'),
    //                     'mk_bulan'          => data_get($dataApi, 'MK_BULAN'),
    //                     'gaji_pokok'        => data_get($dataApi, 'Gaji_Pokok') ?? 0,
    //                     'tipe_jabatan'      => data_get($dataApi, 'TIPE_JABATAN'),
    //                     'kode_jabatan'      => data_get($dataApi, 'KODE_JABATAN'),
    //                     'tampil_jabatan'    => data_get($dataApi, 'TAMPIL_JABATAN'),
    //                     'tmt_jabatan'       => $this->normalizeDate(data_get($dataApi, 'TMT_JABATAN')),
    //                     'kode_satuan_kerja' => data_get($dataApi, 'KODE_SATUAN_KERJA'),
    //                     'satker_1'          => data_get($dataApi, 'SATKER_1'),
    //                     'satker_2'          => data_get($dataApi, 'SATKER_2'),
    //                     'kode_satker_2'     => data_get($dataApi, 'KODE_SATKER_2'),
    //                     'satker_3'          => data_get($dataApi, 'SATKER_3'),
    //                     'kode_satker_3'     => data_get($dataApi, 'KODE_SATKER_3'),
    //                     'satker_4'          => data_get($dataApi, 'SATKER_4'),
    //                     'kode_satker_4'     => data_get($dataApi, 'KODE_SATKER_4'),
    //                     'satker_5'          => data_get($dataApi, 'SATKER_5'),
    //                     'kode_satker_5'     => data_get($dataApi, 'KODE_SATKER_5'),
    //                     'kode_grup_satuan_kerja' => data_get($dataApi, 'KODE_GRUP_SATUAN_KERJA'),
    //                     'grup_satuan_kerja'      => data_get($dataApi, 'GRUP_SATUAN_KERJA'),
    //                     'keterangan_satuan_kerja' => data_get($dataApi, 'KETERANGAN_SATUAN_KERJA'),
    //                     'status_kawin'      => data_get($dataApi, 'STATUS_KAWIN'),
    //                     'alamat_1'          => data_get($dataApi, 'ALAMAT_1'),
    //                     'alamat_2'          => data_get($dataApi, 'ALAMAT_2'),
    //                     'telepon'           => data_get($dataApi, 'TELEPON'),
    //                     'no_hp'             => data_get($dataApi, 'NO_HP'),
    //                     'email'             => data_get($dataApi, 'EMAIL'),
    //                     'kab_kota'          => data_get($dataApi, 'KAB_KOTA'),
    //                     'provinsi'          => data_get($dataApi, 'PROVINSI'),
    //                     'kode_pos'          => data_get($dataApi, 'KODE_POS'),
    //                     'kode_lokasi'       => data_get($dataApi, 'KODE_LOKASI'),
    //                     'iso'               => data_get($dataApi, 'ISO'),
    //                     'kode_pangkat'      => data_get($dataApi, 'KODE_PANGKAT'),
    //                     'keterangan'        => data_get($dataApi, 'KETERANGAN'),
    //                     'tmt_pangkat_yad'   => $this->normalizeDate(data_get($dataApi, 'tmt_pangkat_yad')),
    //                     'tmt_kgb_yad'       => $this->normalizeDate(data_get($dataApi, 'tmt_kgb_yad')),
    //                     'usia_pensiun'      => data_get($dataApi, 'USIA_PENSIUN'),
    //                     'tmt_pensiun'       => $this->normalizeDate(data_get($dataApi, 'TMT_PENSIUN')),
    //                     'mk_tahun_1'        => data_get($dataApi, 'MK_TAHUN_1'),
    //                     'mk_bulan_1'        => data_get($dataApi, 'MK_BULAN_1'),
    //                     'nsm'               => data_get($dataApi, 'NSM'),
    //                     'npsn'              => data_get($dataApi, 'NPSN'),
    //                     'kode_kua'          => data_get($dataApi, 'KODE_KUA'),
    //                     'kode_bidang_studi' => data_get($dataApi, 'KODE_BIDANG_STUDI'),
    //                     'bidang_studi'      => data_get($dataApi, 'BIDANG_STUDI'),
    //                     'status_pegawai'    => data_get($dataApi, 'STATUS_PEGAWAI'),
    //                     'lat'               => data_get($dataApi, 'LAT'),
    //                     'lon'               => data_get($dataApi, 'LON'),
    //                     'satker_kelola'     => data_get($dataApi, 'SATKER_KELOLA'),
    //                     'hari_kerja'        => data_get($dataApi, 'HARI_KERJA'),
    //                     'email_dinas'       => data_get($dataApi, 'EMAIL_DINAS'),
    //                     'id'                => (string) \Illuminate\Support\Str::uuid(), 
    //                     'created_at'        => now(),
    //                     'updated_at'        => now(),
    //                 ];

    //                 // MENGGUNAKAN DB TABLE (Bypass Model Validation)
    //                 DB::table('user_details')->insert($detailData);
    //             }

    //             $existingPenugasan = Penugasan::where('user_id', $user->id)
    //                 ->where('status_aktif', 1)
    //                 ->first(); // pakai first supaya bisa ambil detail

    //             if ($existingPenugasan) {

    //                 Log::warning('GAGAL TAMBAH PENUGASAN - SUDAH ADA PENUGASAN AKTIF', [
    //                     'nip_user'          => $user->nip,
    //                     'nama_user'         => $user->name,
    //                     'user_id'           => $user->id,
    //                     'satker_existing'   => $existingPenugasan->satker_id,
    //                     'tanggal_mulai'     => $existingPenugasan->tanggal_mulai,
    //                     'ditambahkan_oleh'  => auth()->id(),
    //                     'ip_address'        => request()->ip(),
    //                 ]);

    //                 return redirect()->back()->with(
    //                     'error',
    //                     'Pegawai dengan NIP ' . $user->nip . 
    //                     ' sudah memiliki penugasan aktif. Tidak dapat menambahkan penugasan baru sebelum yang lama dinonaktifkan.'
    //                 );
    //             }


    //             $user->roles()->syncWithoutDetaching([$request->role_id]);

    //             Log::info('Isi tabel user_roles setelah insert:', [
    //                 'data' => DB::table('user_roles')->get()
    //             ]);
    //             // Simpan Penugasan
    //             $penugasan = Penugasan::create([
    //                 'user_id' => $user->id,
    //                 'satker_id' => $request->satker_id,
    //                 'jabatan_id' => $request->jabatan_id,
    //                 'jenis_penugasan_id' => $request->jenis_penugasan_id,
    //                 'tanggal_mulai' => $request->tanggal_mulai,
    //                 'tanggal_selesai' => $request->tanggal_selesai,
    //                 'status_aktif' => $request->has('status_aktif') ? 1 : 0,
    //             ]);

    //             // Log Database
    //             LogSistem::create([
    //                 'aksi' => 'CREATE',
    //                 'nama_tabel' => 'penugasan',
    //                 'data_id' => $penugasan->id,
    //                 'perubahan' => 'Menambahkan penugasan baru untuk NIP: ' . $user->nip,
    //                 'user_id' => auth()->id(),
    //             ]);

    //             return redirect()->back()->with('success', 'Data penugasan berhasil ditambah!');

    //         } catch (\Exception $e) {
    //             Log::error("CRITICAL ERROR di PenugasanStore:", [
    //                 'nip_input' => $request->user_nip,
    //                 'pesan' => $e->getMessage(),
    //                 'file' => $e->getFile(),
    //                 'line' => $e->getLine()
    //             ]);
    //             return redirect()->back()->with('error', 'Gagal memproses data: ' . $e->getMessage());
    //         }
    //     });
    // }

public function store(Request $request)
    {
        // Validasi semua field termasuk detailData
        $request->validate([
            'user_nip' => 'required|string',
            'satker_id' => 'required|exists:satker,id',
            'satker_kode' => 'required|exists:satker,kode_satker',
            'jabatan_id' => 'nullable|exists:jabatan,id',
            'jenis_penugasan_id' => 'nullable|exists:m_jenis_penugasan,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status_aktif' => 'nullable|boolean',
            'role_id' => 'required|exists:m_roles,id',
            // validasi detailData
            'nip' => 'nullable|string',
            'nip_baru' => 'nullable|string',
            'name' => 'nullable|string',
            'nama_lengkap' => 'nullable|string',
            'agama' => 'nullable|string',
            'tempat_lahir' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|string',
            'pendidikan' => 'nullable|string',
            'jenjang_pendidikan' => 'nullable|string',
            'kode_level_jabatan' => 'nullable|string',
            'level_jabatan' => 'nullable|string',
            'pangkat' => 'nullable|string',
            'gol_ruang' => 'nullable|string',
            'tmt_cpns' => 'nullable|date',
            'tmt_pangkat' => 'nullable|date',
            'mk_tahun' => 'nullable|integer',
            'mk_bulan' => 'nullable|integer',
            'gaji_pokok' => 'nullable|numeric',
            'tipe_jabatan' => 'nullable|string',
            'kode_jabatan' => 'nullable|string',
            'tampil_jabatan' => 'nullable|string',
            'tmt_jabatan' => 'nullable|date',
            'kode_satuan_kerja' => 'nullable|string',
            'satker_1' => 'nullable|string',
            'satker_2' => 'nullable|string',
            'kode_satker_2' => 'nullable|string',
            'satker_3' => 'nullable|string',
            'kode_satker_3' => 'nullable|string',
            'satker_4' => 'nullable|string',
            'kode_satker_4' => 'nullable|string',
            'satker_5' => 'nullable|string',
            'kode_satker_5' => 'nullable|string',
            'kode_grup_satuan_kerja' => 'nullable|string',
            'grup_satuan_kerja' => 'nullable|string',
            'keterangan_satuan_kerja' => 'nullable|string',
            'status_kawin' => 'nullable|string',
            'alamat_1' => 'nullable|string',
            'alamat_2' => 'nullable|string',
            'telepon' => 'nullable|string',
            'no_hp' => 'nullable|string',
            'email' => 'nullable|string',
            'kab_kota' => 'nullable|string',
            'provinsi' => 'nullable|string',
            'kode_pos' => 'nullable|string',
            'kode_lokasi' => 'nullable|string',
            'iso' => 'nullable|string',
            'kode_pangkat' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'tmt_pangkat_yad' => 'nullable|date',
            'tmt_kgb_yad' => 'nullable|date',
            'usia_pensiun' => 'nullable|integer',
            'tmt_pensiun' => 'nullable|date',
            'mk_tahun_1' => 'nullable|integer',
            'mk_bulan_1' => 'nullable|integer',
            'nsm' => 'nullable|string',
            'npsn' => 'nullable|string',
            'kode_kua' => 'nullable|string',
            'kode_bidang_studi' => 'nullable|string',
            'bidang_studi' => 'nullable|string',
            'status_pegawai' => 'nullable|string',
            'lat' => 'nullable|string',
            'lon' => 'nullable|string',
            'satker_kelola' => 'nullable|string',
            'hari_kerja' => 'nullable|string',
            'email_dinas' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            try {
                $nipFinal = $request->nip_baru ?? $request->user_nip;
                $namaFinal = $request->nama_lengkap ?? $request->name ?? 'Tanpa Nama';
                $emailFinal = $request->email_dinas ?? $request->email ?? ($nipFinal . '@kemenag.go.id');

                $user = User::firstOrCreate(
                    ['nip' => $nipFinal],
                    [
                        'name' => $namaFinal,
                        'email' => $emailFinal,
                        'password' => bcrypt('12345678'),
                        'satker_id' => $request->satker_id,
                    ]
                );

                $isAssigningDefinitif = ($request->jenis_penugasan_id == 1);

                $userActivePenugasans = Penugasan::where('user_id', $user->id)
                                        ->where('status_aktif', 1)
                                        ->get();

                if ($userActivePenugasans->isNotEmpty()) {
                    $isCurrentlyDefinitif = $userActivePenugasans->contains('jenis_penugasan_id', 1);

                    // Aturan 1: Sudah Definitif -> Ditolak
                    if ($isCurrentlyDefinitif) {
                        $msg = 'Gagal: Pegawai ini sedang menjabat sebagai Definitif aktif. Pejabat Definitif tidak bisa merangkap jabatan lain.';
                        return $request->wantsJson() 
                            ? response()->json(['success' => false, 'message' => $msg], 400) 
                            : redirect()->back()->with('error', $msg);
                    }

                    // Aturan 2: Masih Plt/Plh tapi mau diangkat Definitif -> Ditolak
                    if ($isAssigningDefinitif) {
                        $msg = 'Gagal: Pegawai masih memiliki tugas Plt/Plh aktif. Selesaikan tugas tersebut terlebih dahulu sebelum diangkat sebagai Definitif.';
                        return $request->wantsJson() 
                            ? response()->json(['success' => false, 'message' => $msg], 400) 
                            : redirect()->back()->with('error', $msg);
                    }
                }

                // Aturan 3: Kursi Satker sudah terisi Definitif (Baik yang Aktif maupun yang sedang Cuti) -> Ditolak
                if ($isAssigningDefinitif) {
                    $existingDefinitifSatker = Penugasan::where('satker_id', $request->satker_id)
                        ->where('jenis_penugasan_id', 1)
                        ->where(function ($query) {
                            // Cek jika dia Aktif bekerja
                            $query->where('status_aktif', 1)
                                  // ATAU Cek jika dia sedang Cuti (Status 0, tapi punya tgl cuti dan belum selesai tugas permanen)
                                  ->orWhere(function ($subQuery) {
                                      $subQuery->where('status_aktif', 0)
                                               ->whereNotNull('tanggal_selesai_cuti')
                                               ->whereNull('tanggal_selesai');
                                  });
                        })
                        ->first();

                    if ($existingDefinitifSatker) {
                        // Tentukan pesan error yang spesifik agar Admin paham
                        $isSedangCuti = ($existingDefinitifSatker->status_aktif == 0 && $existingDefinitifSatker->tanggal_selesai_cuti != null);
                        
                        if ($isSedangCuti) {
                            $msg = 'Gagal: Pejabat Definitif di Satker ini sedang dalam masa Cuti. Anda hanya dapat menugaskan Plt atau Plh untuk menggantikan sementara.';
                        } else {
                            $msg = 'Gagal: Satker ini sudah memiliki Pejabat Definitif yang aktif. Silakan akhiri tugas pejabat sebelumnya secara permanen (Selesai Tugas) terlebih dahulu.';
                        }

                        return $request->wantsJson() 
                            ? response()->json(['success' => false, 'message' => $msg], 400) 
                            : redirect()->back()->with('error', $msg);
                    }
                }

                // Simpan semua detail pegawai ke tabel user_details
                $detailData = $request->only([
                    'nip', 'nip_baru', 'nama', 'nama_lengkap', 'agama',
                    'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin',
                    'pendidikan', 'jenjang_pendidikan', 'kode_level_jabatan',
                    'level_jabatan', 'pangkat', 'gol_ruang', 'tmt_cpns',
                    'tmt_pangkat', 'mk_tahun', 'mk_bulan', 'gaji_pokok',
                    'tipe_jabatan', 'kode_jabatan', 'tampil_jabatan', 'tmt_jabatan',
                    'kode_satuan_kerja', 'satker_1', 'satker_2', 'kode_satker_2',
                    'satker_3', 'kode_satker_3', 'satker_4', 'kode_satker_4',
                    'satker_5', 'kode_satker_5', 'kode_grup_satuan_kerja',
                    'grup_satuan_kerja', 'keterangan_satuan_kerja', 'status_kawin',
                    'alamat_1', 'alamat_2', 'telepon', 'no_hp', 'email', 'kab_kota',
                    'provinsi', 'kode_pos', 'kode_lokasi', 'iso', 'kode_pangkat',
                    'keterangan', 'tmt_pangkat_yad', 'tmt_kgb_yad', 'usia_pensiun',
                    'tmt_pensiun', 'mk_tahun_1', 'mk_bulan_1', 'nsm', 'npsn',
                    'kode_kua', 'kode_bidang_studi', 'bidang_studi', 'status_pegawai',
                    'lat', 'lon', 'satker_kelola', 'hari_kerja', 'email_dinas'
                ]);

                $dateFields = [
                    'tmt_cpns',
                    'tmt_pangkat',
                    'tmt_pangkat_yad',
                    'tmt_kgb_yad',
                    'tmt_pensiun',
                ];

                foreach ($dateFields as $field) {
                    if (!empty($detailData[$field])) {
                        try {
                            $detailData[$field] = \Carbon\Carbon::parse($detailData[$field])->format('Y-m-d');
                        } catch (\Exception $e) {
                            $detailData[$field] = null;
                        }
                    }
                }

                // ==========================================
                // PERBAIKAN LOGIKA INSERT/UPDATE USER DETAIL
                // ==========================================
                $detailData['nama'] = $namaFinal;
                $detailData['nip'] = $request->nip ?? $request->user_nip; // Biarkan NIP bawaan apa adanya
                $detailData['nip_baru'] = $nipFinal; // Pasti 18 digit
                $detailData['updated_at'] = now();

                // Cari berdasarkan nip_baru (Sangat akurat untuk Kemenag)
                $existingDetail = DB::table('user_details')->where('nip_baru', $nipFinal)->first();

                if ($existingDetail) {
                    // Jika sudah ada, cukup UPDATE (Jangan buat ID/UUID baru)
                    DB::table('user_details')->where('nip_baru', $nipFinal)->update($detailData);
                } else {
                    // Jika belum ada, buat baru (INSERT) dan generate UUID
                    $detailData['id'] = (string) \Illuminate\Support\Str::uuid();
                    $detailData['created_at'] = now();
                    DB::table('user_details')->insert($detailData);
                }

                // $user->roles()->syncWithoutDetaching([$request->role_id]);
                $user->roles()->sync([$request->role_id]);

                Log::info('Isi tabel user_roles setelah insert:', [
                    'data' => DB::table('user_roles')->get()
                ]);
                
                // Simpan Penugasan 
                $penugasan = Penugasan::create([
                    'user_id' => $user->id,
                    'satker_id' => $request->satker_id,
                    'jabatan_id' => $request->jabatan_id,
                    'jenis_penugasan_id' => $request->jenis_penugasan_id,
                    'tanggal_mulai' => $request->tanggal_mulai,
                    'tanggal_selesai' => $request->tanggal_selesai,
                    'status_aktif' => $request->has('status_aktif') ? 1 : 0,
                ]);

                // Log sistem
                LogSistem::create([
                    'aksi' => 'CREATE',
                    'nama_tabel' => 'penugasan',
                    'data_id' => $penugasan->id,
                    'perubahan' => 'Menambahkan penugasan baru untuk NIP: ' . $user->nip,
                    'user_id' => auth()->id(),
                ]);

                // JIKA SUKSES
                $msgSuccess = 'Data penugasan berhasil ditambah!';
                return $request->wantsJson() 
                    ? response()->json(['success' => true, 'message' => $msgSuccess]) 
                    : redirect()->back()->with('success', $msgSuccess);

            } catch (\Exception $e) {
                Log::error("CRITICAL ERROR di PenugasanStore:", [
                    'nip_input' => $request->user_nip,
                    'pesan' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);

                $pesanAman = 'Gagal memproses data penugasan. Terjadi kendala sistem, silakan coba lagi nanti.';

                // ==========================================
                // PERBAIKAN FILTER PESAN ERROR
                // ==========================================
                if (str_contains($e->getMessage(), 'user_roles_user_id_unique')) {
                    $pesanAman = 'Gagal menyimpan: Pegawai ini sudah didaftarkan dan memiliki hak akses (role) yang aktif di sistem.';
                } elseif (str_contains($e->getMessage(), 'Connection refused') || str_contains($e->getMessage(), 'cURL error')) {
                    $pesanAman = 'Gagal menyimpan: Terjadi masalah koneksi atau jaringan ke server Kemenag.';
                } elseif (str_contains($e->getMessage(), 'duplicate key value violates unique constraint')) {
                    $pesanAman = 'Gagal menyimpan: Terdapat data ganda (duplikat) di sistem. Harap periksa kembali NIP Pegawai.';
                } else {
                    // Munculkan pesan asli secara aman jika di luar tebakan kita
                    $pesanAman = 'Gagal menyimpan: ' . explode(' (Connection', $e->getMessage())[0];
                }

                // JIKA ERROR
                return $request->wantsJson() 
                    ? response()->json(['success' => false, 'message' => $pesanAman], 400) 
                    : redirect()->back()->with('error', $pesanAman);
            }
        });
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'satker_id' => 'required|exists:satker,id',
            'jabatan_id' => 'required|exists:jabatan,id',
            'jenis_penugasan_id' => 'required|exists:m_jenis_penugasan,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status_aktif' => 'nullable|boolean',
        ]);

        $penugasan = Penugasan::findOrFail($id);
        
        $data = $request->all();
        // Logika yang sama untuk checkbox pada saat update
        $data['status_aktif'] = $request->has('status_aktif') ? 1 : 0;

        $penugasan->update($data);
        LogSistem::create([
            'aksi' => 'UPDATE',
            'nama_tabel' => 'penugasan',
            'data_id' => $penugasan->id,
            'perubahan' => 'Memperbarui penugasan untuk user ID: ' . $penugasan->user_id,
            'user_id' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Data penugasan berhasil diubah!');
    }

    public function unassign(Request $request, $id)
    {
        try {
            $penugasan = Penugasan::findOrFail($id);

            if ($request->jenis_aksi === 'cuti') {
                $penugasan->update([
                    'status_aktif'         => 0,
                    'tanggal_mulai_cuti'   => $request->tanggal_mulai_cuti,
                    'tanggal_selesai_cuti' => $request->tanggal_selesai_cuti,
                ]);
                $pesan = 'Pejabat berhasil dicutikan.';
            } 

            else {
                $penugasan->update([
                    'status_aktif'         => 0,
                    'tanggal_selesai'      => $request->tanggal_selesai ? \Carbon\Carbon::parse($request->tanggal_selesai)->format('Y-m-d') : now(),
                    'tanggal_mulai_cuti'   => null,
                    'tanggal_selesai_cuti' => null,
                ]);
                $pesan = 'Tugas berhasil diakhiri.';
            }

            return response()->json([
                'success' => true,
                'message' => $pesan
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status.'
            ], 500);
        }
    }


    public function destroy($id)
    {
        Penugasan::findOrFail($id)->delete();
        $penugasan = Penugasan::findOrFail($id);

        LogSistem::create([
            'aksi' => 'DELETE',
            'nama_tabel' => 'penugasan',
            'data_id' => $penugasan->id,
            'perubahan' => 'Menghapus penugasan untuk user ID: ' . $penugasan->user_id,
            'user_id' => auth()->id(),
        ]);
        return redirect()->back()->with('success', 'Data penugasan berhasil dihapus!');
    }

    function normalizeDate($date)
    {
        if (!$date) return null;

        try {
            return Carbon::parse($date)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

}