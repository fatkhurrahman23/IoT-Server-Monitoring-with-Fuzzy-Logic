<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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

    public static function getRecent(int $limit = 30): iterable
    {
        return Cache::remember('telemetry_recent_' . $limit, 1, function () use ($limit) {
            return static::latest()->take($limit)->get()->reverse()->values();
        });
    }

    public static function getLatest(): ?self
    {
        return Cache::remember('telemetry_latest', 1, function () {
            return static::latest()->first();
        });
    }
}

