<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wilayah extends Model
{
    use HasUuids;

    protected $table = 'wilayah';
    protected $fillable = ['kode_wilayah', 'nama_wilayah', 'tingkat_wilayah_id', 'parent_wilayah_id'];

    public function tingkat()
    {
        return $this->belongsTo(MTingkatWilayah::class, 'tingkat_wilayah_id');
    }

    public function parent()
    {
        return $this->belongsTo(Wilayah::class, 'parent_wilayah_id');
    }

    public function children()
    {
        return $this->hasMany(Wilayah::class, 'parent_wilayah_id');
    }
}