<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class BulkingDetail extends Model
{
    use HasUuids;

    protected $fillable = [
        'bulking_id',
        'user_detail_id',
        'user_id',
        'nip',
        'status',
        'message'
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function bulking()
    {
        return $this->belongsTo(Bulking::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}