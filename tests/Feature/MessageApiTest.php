<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessMessageJob;

class MessageApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_dispatches_job_and_creates_message()
    {
        Queue::fake();

        $payload = ['sender_id' => 1, 'message' => 'Hello world'];

        $response = $this->postJson('/api/messages', $payload);

        $response->assertStatus(201)->assertJsonStructure(['id']);

        $this->assertDatabaseHas('messages', [
            'sender_id' => 1,
            'body' => 'Hello world',
            'status' => 'pending'
        ]);

        Queue::assertPushed(ProcessMessageJob::class, function ($job) use ($response) {
            return $job->messageId === $response->json('id');
        });
    }
}
