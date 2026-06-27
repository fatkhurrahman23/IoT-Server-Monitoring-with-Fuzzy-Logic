<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelemetryLog extends Model
{
    protected $fillable = [
        'temp',
        'cpu_load',
        'server_loads',
        'ac_target',
        'status',
    ];

    protected $casts = [
        'temp' => 'float',
        'cpu_load' => 'float',
        'server_loads' => 'array',
        'ac_target' => 'float',
    ];
}
