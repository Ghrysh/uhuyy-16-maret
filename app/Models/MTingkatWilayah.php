<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MTingkatWilayah extends Model
{
    protected $table = 'm_tingkat_wilayah';
    public $timestamps = false;
    protected $fillable = ['nama'];
}