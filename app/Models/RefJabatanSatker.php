<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Penting untuk auto-generate UUID
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefJabatanSatker extends Model
{
    use HasUuids; // Menginstruksikan Laravel untuk otomatis mengisi UUID saat create

    protected $table = 'ref_jabatan_satker';

    // Karena UUID bukan integer, kita harus set ini
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'tingkat_wilayah_id',
        'parent_id',
        'key_jabatan',
        'label_jabatan',
        'kode_dasar',
        'is_increment',
    ];

    /**
     * Relasi ke Tingkat Wilayah (Pusat, PTKN, Provinsi)
     */
    public function tingkatWilayah(): BelongsTo
    {
        return $this->belongsTo(TingkatWilayah::class, 'tingkat_wilayah_id');
    }

    /**
     * Relasi ke Atasan/Parent (Self-referencing)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(RefJabatanSatker::class, 'parent_id');
    }

    /**
     * Relasi ke Bawahan/Children (Self-referencing)
     * Digunakan untuk cascading dropdown
     */
    public function children(): HasMany
    {
        return $this->hasMany(RefJabatanSatker::class, 'parent_id');
    }

    /**
     * Scope untuk mengambil data berdasarkan tingkat wilayah
     * Contoh: RefJabatanSatker::wilayah(2)->get();
     */
    public function scopeWilayah($query, $id)
    {
        return $query->where('tingkat_wilayah_id', $id);
    }

    /**
     * Scope untuk mengambil jabatan yang tidak punya parent (level teratas)
     */
    public function scopeIsRoot($query)
    {
        return $query->whereNull('parent_id');
    }
}