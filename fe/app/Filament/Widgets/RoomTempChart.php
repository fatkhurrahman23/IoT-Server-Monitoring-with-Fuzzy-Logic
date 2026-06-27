<?php

namespace App\Filament\Widgets;

use App\Models\TelemetryLog;
use Filament\Widgets\ChartWidget;

class RoomTempChart extends ChartWidget
{
    protected ?string $heading = 'Histori Suhu Ruangan';
    protected ?string $pollingInterval = '1s';
    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $logs = TelemetryLog::getRecent(30);

        $labels = $logs->map(fn ($log) => $log->created_at->format('H:i:s'))->toArray();
        $tempData = $logs->map(fn ($log) => round($log->temp, 1))->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Suhu Ruangan (°C)',
                    'data' => $tempData,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
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
                    'min' => 10,
                    'max' => 45,
                ],
            ],
        ];
    }
}
