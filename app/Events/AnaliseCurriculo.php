<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnaliseCurriculo implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $candidaturaId;

    public function __construct(int $candidaturaId)
    {
        $this->candidaturaId = $candidaturaId;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('candidaturas'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'AnaliseConcluida';
    }
}
