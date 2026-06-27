<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcControlLog extends Model
{
    protected $fillable = [
        'temp',
        'cpu_load',
        'ac_target',
    ];

    protected $casts = [
        'temp' => 'float',
        'cpu_load' => 'float',
        'ac_target' => 'float',
    ];
}
