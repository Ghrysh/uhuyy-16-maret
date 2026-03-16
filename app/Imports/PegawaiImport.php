<?php

namespace App\Imports;

use App\Jobs\ImportPegawaiJob;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;

class PegawaiImport implements ToModel, WithChunkReading, WithStartRow
{
    public function model(array $row)
    {
        $nip = isset($row[0]) ? trim($row[0]) : null;
        if ($nip && is_numeric($nip)) {
            ImportPegawaiJob::dispatch($nip, true);
        }
        return null;
    }

    public function startRow(): int
    {
        return 2; // Lewati header, mulai baca dari baris ke-2
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}