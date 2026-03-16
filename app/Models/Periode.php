<?php

namespace App\Models; // <--- WAJIB ADA

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Tambahkan ini
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Periode extends Model
{
    use HasFactory, HasUuids; // Gunakan HasUuids bawaan Laravel

    protected $table = 'periodes';
    
    // Karena menggunakan HasUuids, Anda tidak perlu lagi manual boot Str::uuid()
    // Laravel akan otomatis mengisi ID saat proses creating.
    
    protected $fillable = [
        'nama_periode',
        'keterangan'
    ];

    protected $keyType = 'string';
    public $incrementing = false;
}