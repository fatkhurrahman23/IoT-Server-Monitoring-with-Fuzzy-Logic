<?php

namespace App\Filament\Widgets;

use App\Models\TelemetryLog;
use Filament\Widgets\ChartWidget;

class CpuLoadChart extends ChartWidget
{
    protected ?string $heading = 'Histori Beban CPU';
    protected ?string $pollingInterval = '3s';
    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $logs = TelemetryLog::latest()->take(30)->get()->reverse()->values();

        $labels = $logs->map(fn ($log) => $log->created_at->format('H:i:s'))->toArray();
        $cpuData = $logs->map(fn ($log) => round($log->cpu_load, 1))->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Beban CPU (%)',
                    'data' => $cpuData,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
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
                    'min' => 0,
                    'max' => 100,
                ],
            ],
        ];
    }
}
