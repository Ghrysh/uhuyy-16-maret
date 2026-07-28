<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PegawaiPeriode extends Model
{
    use HasUuids;

    protected $table = 'pegawai_periodes';

    protected $fillable = [
        'user_id',
        'satker_id',
        'periode_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function satker()
    {
        return $this->belongsTo(Satker::class, 'satker_id');
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }
}
