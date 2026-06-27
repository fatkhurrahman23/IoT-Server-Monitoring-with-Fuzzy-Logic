<?php

namespace App\Filament\Widgets;

use App\Models\TelemetryLog;
use Filament\Widgets\ChartWidget;

class AcTargetChart extends ChartWidget
{
    protected ?string $heading = 'Histori Target Suhu AC';
    protected ?string $pollingInterval = '3s';
    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $logs = TelemetryLog::latest()->take(30)->get()->reverse()->values();

        $labels = $logs->map(fn ($log) => $log->created_at->format('H:i:s'))->toArray();
        $acData = $logs->map(fn ($log) => round($log->ac_target, 1))->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Target Suhu AC (°C)',
                    'data' => $acData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => 'start',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'min' => 15,
                    'max' => 30,
                ],
            ],
        ];
    }
}
