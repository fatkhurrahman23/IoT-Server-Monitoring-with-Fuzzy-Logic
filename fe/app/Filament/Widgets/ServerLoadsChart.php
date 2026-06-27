<?php

namespace App\Filament\Widgets;

use App\Models\TelemetryLog;
use Filament\Widgets\ChartWidget;

class ServerLoadsChart extends ChartWidget
{
    protected ?string $heading = 'Histori Beban CPU per Server';
    protected ?string $pollingInterval = '1s';
    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $logs = TelemetryLog::getRecent(30);

        $labels = $logs->map(fn ($log) => $log->created_at->format('H:i:s'))->toArray();

        $colors = ['#ef4444', '#f59e0b', '#10b981', '#8b5cf6', '#06b6d4'];

        $datasets = [];
        for ($i = 0; $i < 5; $i++) {
            $datasets[] = [
                'label' => "Server " . ($i + 1),
                'data' => $logs->map(fn ($log) => $log->server_loads[$i] ?? 0)->toArray(),
                'borderColor' => $colors[$i],
                'backgroundColor' => 'transparent',
                'fill' => false,
                'tension' => 0.2,
            ];
        }

        return [
            'datasets' => $datasets,
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
                    'min' => 0,
                    'max' => 100,
                ],
            ],
        ];
    }
}
