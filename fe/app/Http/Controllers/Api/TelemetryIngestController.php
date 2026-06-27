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
            'servers' => 'nullable|array|max:10',
            'servers.*' => 'numeric|min:0|max:100',
        ]);

        $log = TelemetryLog::create([
            'temp' => $validated['temp'],
            'cpu_load' => $validated['cpu_load'],
            'server_loads' => $validated['servers'] ?? [$validated['cpu_load']],
            'ac_target' => $validated['ac_target'],
            'status' => $request->input('status'),
        ]);

        broadcast(new TelemetryUpdated(
            temp: $log->temp,
            cpu_load: $log->cpu_load,
            servers: $log->server_loads ?? [],
            ac_target: $log->ac_target,
            status: $log->status,
            alert: $request->input('alert', false),
            timestamp: $log->created_at->toISOString(),
        ));

        return response()->json(['status' => 'ok', 'id' => $log->id], 201);
    }
}
