<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TelemetryUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public float $temp,
        public float $cpu_load,
        public array $servers,
        public float $ac_target,
        public ?string $status,
        public bool $alert,
        public string $timestamp,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('telemetry'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'telemetry.updated';
    }
}
