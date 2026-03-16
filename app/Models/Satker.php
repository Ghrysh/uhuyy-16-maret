<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Satker extends Model
{
    use HasUuids;

    protected $table = 'satker';
    protected $fillable = [
        'kode_satker', 'nama_satker', 'jenis_satker_id', 
        'wilayah_id', 'parent_satker_id', 'status_aktif', 'keterangan', 'periode_id', 'ref_jabatan_satker_id',
    ];

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class);
    }

    public function penugasan(): HasMany
    {
        // Pastikan foreign key 'satker_id' ada di tabel penugasan
        return $this->hasMany(Penugasan::class, 'satker_id');
    }

    public function children()
    {
        // Memanggil children dan eselon secara rekursif untuk performa optimal (Eager Loading)
        return $this->hasMany(Satker::class, 'parent_satker_id', 'id')->with(['children', 'eselon'])
                ->orderBy('kode_satker', 'asc');
    }

    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    public function parent()
    {
        return $this->belongsTo(Satker::class, 'parent_satker_id');
    }

    public function eselon()
    {
        // Karena Anda tidak punya model untuk JenisSatker, kita arahkan ke tabelnya langsung
        return $this->belongsTo(JenisSatker::class, 'jenis_satker_id'); 
    }

    public function pegawais(): HasMany
    {
        // users.satker_id (varchar) -> satker.kode_satker (varchar)
        return $this->hasMany(User::class, 'satker_id', 'kode_satker');
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function refJabatanSatker()
    {
        return $this->belongsTo(RefJabatanSatker::class, 'ref_jabatan_satker_id');
    }

}