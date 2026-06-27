<?php

use App\Http\Controllers\Api\TelemetryIngestController;
use Illuminate\Support\Facades\Route;

Route::post('/telemetry/ingest', TelemetryIngestController::class);
