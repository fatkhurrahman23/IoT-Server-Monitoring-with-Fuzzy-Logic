<?php

namespace App\Http\Controllers\Api;

use App\Events\TelemetryUpdated;
use App\Http\Controllers\Controller;
use App\Models\TelemetryLog;
use Illuminate\Http\Request;

class TelemetryIngestController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'temp' => 'required|numeric|min:0|max:60',
            'cpu_load' => 'required|numeric|min:0|max:100',
            'ac_target' => 'required|numeric|min:10|max:35',
        ]);

        $log = TelemetryLog::create($validated);

        broadcast(new TelemetryUpdated(
            temp: $log->temp,
            cpu_load: $log->cpu_load,
            ac_target: $log->ac_target,
            timestamp: $log->created_at->toISOString(),
        ));

        return response()->json(['status' => 'ok', 'id' => $log->id], 201);
    }
}
