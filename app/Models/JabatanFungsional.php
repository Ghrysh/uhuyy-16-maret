<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import Trait
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JabatanFungsional extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'jabatan_fungsionals';

    protected $fillable = [
        'kode',
        'name',
    ];

    // Beritahu Laravel bahwa Primary Key bukan incrementing integer
    public $incrementing = false;
    protected $keyType = 'string';
}