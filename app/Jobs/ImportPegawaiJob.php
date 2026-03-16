<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\UserDetail;
use App\Imports\PegawaiImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ImportPegawaiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payload; 
    protected $isSingleNip;
    public $timeout = 3600; 

    /**
     * @param mixed $payload Bisa berupa Path File (String) atau NIP (String)
     * @param bool $isSingleNip Flag untuk menentukan mode proses
     */
    public function __construct($payload, $isSingleNip = false)
    {
        $this->payload = $payload;
        $this->isSingleNip = $isSingleNip;
    }

    public function handle(): void
    {
        if ($this->isSingleNip) {
            // MODE B: Proses NIP Satuan ke API & Database
            $this->processNip($this->payload);
        } else {
            // MODE A: Pecah File Excel (Chunking)
            $this->handleExcelFile();
        }
    }

    private function handleExcelFile()
    {
        $fullPath = storage_path('app/' . $this->payload);
        Log::info("=== [MODE FILE] MEMULAI PECAH CHUNK EXCEL ===");

        try {
            // Matikan kalkulasi otomatis untuk mempercepat load
            config(['excel.imports.read_only' => true]);
            
            // Gunakan Excel::import dengan paksa format XLSX dan abaikan formatting
            Excel::import(
                new PegawaiImport, 
                $fullPath, 
                null, 
                \Maatwebsite\Excel\Excel::XLSX
            );

            Log::info("=== [MODE FILE] BERHASIL PECAH CHUNK ===");
            Storage::delete($this->payload);

        } catch (\Exception $e) {
            Log::error("Gagal di Chunking: " . $e->getMessage());
            // Jika gagal, pastikan file tetap dihapus agar tidak memenuhi disk
            Storage::delete($this->payload);
        }
    }

    private function processNip($nip)
    {
        try {
            Log::info("[SINGLE NIP] Mengolah: {$nip}");
            
            $response = Http::timeout(30)->get("http://localhost:8092/admin/pegawai/search?nip={$nip}");

            if (!$response->successful()) {
                Log::error("API Error ({$response->status()}) untuk NIP: {$nip}");
                return;
            }

            $result = $response->json();
            
            if (!$result['success'] || !isset($result['data']['data'])) {
                Log::warning("NIP {$nip} tidak ditemukan di API.");
                return;
            }

            $d = $result['data']['data'];
            $apiNipBaru = $d['NIP_BARU'];

            DB::transaction(function () use ($d, $apiNipBaru) {
                $user = User::updateOrCreate(
                    ['nip' => $apiNipBaru],
                    [
                        'name' => $d['NAMA_LENGKAP'] ?? $d['NAMA'],
                        'email' => $d['EMAIL_DINAS'] ?? $d['EMAIL'] ?? ($apiNipBaru . '@kemenag.go.id'),
                        'password' => bcrypt('password123'),
                    ]
                );

                $parseDate = function($date) {
                    if (!$date) return null;
                    try {
                        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $date)) {
                            return Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
                        }
                        return Carbon::parse($date)->format('Y-m-d');
                    } catch (\Exception $e) { return null; }
                };

                UserDetail::updateOrCreate(
                    ['id' => $user->id], // Menghubungkan UUID User ke ID Detail
                    [
                        'nip_baru'           => $apiNipBaru,
                        'nip'                => $d['NIP'],
                        'nama'               => $d['NAMA'],
                        'nama_lengkap'       => $d['NAMA_LENGKAP'],
                        'agama'              => $d['AGAMA'],
                        'tempat_lahir'       => $d['TEMPAT_LAHIR'],
                        'tanggal_lahir'      => $parseDate($d['TANGGAL_LAHIR']),
                        'jenis_kelamin'      => $d['JENIS_KELAMIN'],
                        'pendidikan'         => $d['PENDIDIKAN'],
                        'jenjang_pendidikan' => $d['JENJANG_PENDIDIKAN'],
                        'pangkat'            => $d['PANGKAT'],
                        'gol_ruang'          => $d['GOL_RUANG'],
                        'tmt_cpns'           => $parseDate($d['TMT_CPNS']),
                        'tmt_pangkat'        => $parseDate($d['TMT_PANGKAT']),
                        'mk_tahun'           => $d['MK_TAHUN'],
                        'mk_bulan'           => $d['MK_BULAN'],
                        'gaji_pokok'         => $d['Gaji_Pokok'] ?? 0,
                        'tipe_jabatan'       => $d['TIPE_JABATAN'],
                        'tampil_jabatan'     => $d['TAMPIL_JABATAN'],
                        'tmt_jabatan'        => $parseDate($d['TMT_JABATAN']),
                        'kode_satuan_kerja'  => $d['KODE_SATUAN_KERJA'],
                        'satker_1'           => $d['SATKER_1'],
                        'satker_2'           => $d['SATKER_2'],
                        'satker_3'           => $d['SATKER_3'],
                        'satker_4'           => $d['SATKER_4'],
                        'satker_5'           => $d['SATKER_5'],
                        'keterangan_satuan_kerja' => $d['KETERANGAN_SATUAN_KERJA'],
                        'no_hp'              => $d['NO_HP'],
                        'email'              => $d['EMAIL'],
                        'email_dinas'        => $d['EMAIL_DINAS'],
                        'status_pegawai'     => $d['STATUS_PEGAWAI'],
                        'keterangan'         => $d['KETERANGAN'],
                        'tmt_pensiun'        => $parseDate($d['TMT_PENSIUN']),
                    ]
                );
            });

            Log::info("Sukses Sinkron: {$d['NAMA']}");

        } catch (\Exception $e) {
            Log::error("Gagal NIP {$nip}: " . $e->getMessage());
        }
    }
}