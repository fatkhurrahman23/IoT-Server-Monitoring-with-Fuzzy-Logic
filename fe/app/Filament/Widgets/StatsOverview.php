<?php

namespace App\Filament\Widgets;

use App\Models\TelemetryLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected ?string $pollingInterval = '1s';
    protected int|string|array $columnSpan = ['md' => 3, 'xl' => 3];
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $latest = TelemetryLog::getLatest();

        $temp = $latest ? $latest['temp'] : '--';
        $cpu = $latest ? $latest['cpu_load'] : '--';
        $ac = $latest ? $latest['ac_target'] : '--';
        $status = $latest ? ($latest['status'] ?? 'idle') : 'idle';

        $tempColor = is_numeric($temp) ? $this->getTempColor((float) $temp) : 'gray';
        $cpuColor = is_numeric($cpu) ? $this->getCpuColor((float) $cpu) : 'gray';
        $acColor = is_numeric($ac) ? $this->getAcColor((float) $ac) : 'gray';
        $statusColor = $this->getStatusColor($status);
        $statusLabel = $this->getStatusLabel($status);

        return [
            Stat::make('Suhu Ruangan', is_numeric($temp) ? number_format((float) $temp, 1) . '°C' : '-- °C')
                ->description('Terkini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($tempColor),

            Stat::make('MAX CPU Load', is_numeric($cpu) ? number_format((float) $cpu, 0) . '%' : '-- %')
                ->description('Server dengan beban tertinggi')
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color($cpuColor),

            Stat::make('Target Suhu AC', is_numeric($ac) ? number_format((float) $ac, 1) . '°C' : '-- °C')
                ->description('Output Fuzzy AI')
                ->descriptionIcon('heroicon-m-beaker')
                ->color($acColor),

            Stat::make('Status AC', ucfirst($statusLabel))
                ->description('Status pendinginan')
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color($statusColor),
        ];
    }

    private function getTempColor(float $temp): string
    {
        if ($temp > 35) return 'danger';
        if ($temp > 28) return 'warning';
        return 'success';
    }

    private function getCpuColor(float $cpu): string
    {
        if ($cpu > 85) return 'danger';
        if ($cpu > 60) return 'warning';
        return 'success';
    }

    private function getAcColor(float $ac): string
    {
        if ($ac <= 18) return 'info';
        if ($ac >= 26) return 'warning';
        return 'success';
    }

    private function getStatusColor(string $status): string
    {
        return match ($status) {
            'idle' => 'success',
            'cooling' => 'warning',
            'full' => 'info',
            default => 'gray',
        };
    }

    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'idle' => 'hemat energi',
            'cooling' => 'pendinginan normal',
            'full' => 'pendinginan maksimal',
            default => $status,
        };
    }
}
