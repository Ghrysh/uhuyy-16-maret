<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\ImportPegawaiJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PegawaiImportController extends Controller
{
    public function index()
    {
        return view('admin.pegawai.import');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls,csv|max:102400' 
        ]);

        try {
            // 1. Simpan file ke storage (bukan memprosesnya di sini)
            $file = $request->file('file_excel');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('imports', $fileName);

            // 2. Kirim path file ke Job
            // Kita tidak lagi menggunakan loop di sini agar response instan
            ImportPegawaiJob::dispatch($path);

            return back()->with('success', "File berhasil diunggah! Proses pembacaan data sedang berjalan di latar belakang. Silakan cek berkala.");

        } catch (\Exception $e) {
            Log::error('Gagal mengunggah file Excel: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat mengunggah file.');
        }
    }
}