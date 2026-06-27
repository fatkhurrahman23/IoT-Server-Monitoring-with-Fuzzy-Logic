<?php

namespace App\Filament\Widgets;

use App\Models\TelemetryLog;
use Filament\Widgets\Widget;

class AlertBanner extends Widget
{
    protected ?string $pollingInterval = '1s';
    protected int|string|array $columnSpan = 'full';
    protected string $view = 'filament.widgets.alert-banner';

    protected function getViewData(): array
    {
        $latest = TelemetryLog::getLatest();

        return [
            'alert' => $latest && ($latest['temp'] > 35),
            'temp' => $latest ? $latest['temp'] : 0,
        ];
    }
}
