<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Bulking extends Model
{
    use HasUuids;

    protected $fillable = [
        'type',
        'satker_id',
        'created_by',
        'total_data'
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function details()
    {
        return $this->hasMany(BulkingDetail::class, 'bulking_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}