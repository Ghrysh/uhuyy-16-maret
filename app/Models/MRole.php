<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MRole extends Model
{
    protected $table = 'm_roles';
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