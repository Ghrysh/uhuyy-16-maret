<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MJenisSatker extends Model
{
    protected $table = 'm_jenis_satker';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'nama',
    ];

    public $timestamps = false;
}
