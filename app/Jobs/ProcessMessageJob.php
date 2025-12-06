<?php

namespace App\Jobs;

use App\Events\MessageReceived;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ProcessMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $messageId;

    public function __construct(int $messageId)
    {
        $this->messageId = $messageId;
    }

    public function handle(): void
    {
        $message = Message::find($this->messageId);
        if (! $message) {
            return;
        }

        // sanitize message
        $sanitized = strip_tags($message->body);

        // append metadata
        $metadata = $message->metadata ?? [];
        $metadata['processed_token'] = (string) Str::uuid();
        $metadata['processed_by'] = config('app.name');
        $metadata['processed_at'] = now()->toDateTimeString();

        // update DB
        $message->update([
            'sanitized_body' => $sanitized,
            'status' => 'processed',
            'processed_at' => now(),
            'metadata' => $metadata,
        ]);

        // broadcast event via Reverb
        event(new MessageReceived($message));
    }

    public function failed(\Throwable $exception): void
    {
        $message = Message::find($this->messageId);
        if ($message) {
            $message->update(['status' => 'failed']);
        }
        // Optional: Log exception here
    }
}
