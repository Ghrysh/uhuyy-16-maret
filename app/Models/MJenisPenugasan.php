<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MJenisPenugasan extends Model
{
    protected $table = 'm_jenis_penugasan';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'key', 'nama', 'menus', 'is_assignable', 'regulations'
    ];

    protected $casts = [
        'menus' => 'array',
        'is_assignable' => 'boolean',
        'regulations' => 'array',
    ];
}