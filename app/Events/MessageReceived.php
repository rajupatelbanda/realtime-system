<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class MessageReceived implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('messages.channel');
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'body' => $this->message->sanitized_body ?: $this->message->body,
            'status' => $this->message->status,
            'processed_at' => optional($this->message->processed_at)->toDateTimeString(),
            'created_at' => $this->message->created_at->toDateTimeString(),
            'metadata' => $this->message->metadata,
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.received';
    }
}
