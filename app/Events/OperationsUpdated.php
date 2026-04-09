<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OperationsUpdated implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public string $type,
        public ?int $branchId = null,
        public ?int $orderId = null,
        public ?string $station = null,
        public array $meta = [],
    ) {
    }

    public function broadcastOn(): array
    {
        return [new Channel('restaurant-pos.operations')];
    }

    public function broadcastAs(): string
    {
        return 'operations.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->type,
            'branch_id' => $this->branchId,
            'order_id' => $this->orderId,
            'station' => $this->station,
            'meta' => $this->meta,
            'sent_at' => now()->toIso8601String(),
        ];
    }
}
