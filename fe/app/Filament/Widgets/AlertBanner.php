<?php

namespace App\Filament\Widgets;

use App\Models\TelemetryLog;
use Filament\Widgets\Widget;

class AlertBanner extends Widget
{
    protected static ?string $pollingInterval = '3s';
    protected int|string|array $columnSpan = 'full';
    protected string $view = 'filament.widgets.alert-banner';

    protected function getViewData(): array
    {
        $latest = TelemetryLog::latest()->first();

        return [
            'alert' => $latest && $latest->temp > 35,
            'temp' => $latest ? $latest->temp : 0,
        ];
    }
}
