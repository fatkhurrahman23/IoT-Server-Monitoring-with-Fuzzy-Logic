<?php

namespace App\Filament\Widgets;

use App\Models\TelemetryLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected ?string $pollingInterval = '3s';
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $latest = TelemetryLog::latest()->first();

        $temp = $latest ? $latest->temp : '--';
        $cpu = $latest ? $latest->cpu_load : '--';
        $ac = $latest ? $latest->ac_target : '--';

        $tempColor = is_numeric($temp) ? $this->getTempColor((float) $temp) : 'gray';
        $cpuColor = is_numeric($cpu) ? $this->getCpuColor((float) $cpu) : 'gray';
        $acColor = is_numeric($ac) ? $this->getAcColor((float) $ac) : 'gray';

        return [
            Stat::make('Suhu Ruangan', is_numeric($temp) ? number_format((float) $temp, 1) . '°C' : '-- °C')
                ->description('Terkini')
                ->color($tempColor)
                ->icon('heroicon-o-globe-alt'),

            Stat::make('Beban CPU', is_numeric($cpu) ? number_format((float) $cpu, 0) . '%' : '-- %')
                ->description('Terkini')
                ->color($cpuColor)
                ->icon('heroicon-o-cpu-chip'),

            Stat::make('Target Suhu AC', is_numeric($ac) ? number_format((float) $ac, 1) . '°C' : '-- °C')
                ->description('Output Fuzzy AI')
                ->color($acColor)
                ->icon('heroicon-o-beaker'),
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
}
