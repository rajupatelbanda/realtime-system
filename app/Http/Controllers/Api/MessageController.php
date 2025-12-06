<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Jobs\ProcessMessageJob;
use App\Models\Message;
use Illuminate\Http\JsonResponse;

class MessageController extends Controller
{
    public function store(StoreMessageRequest $request): JsonResponse
    {
        $data = $request->validated();

        $message = Message::create([
            'sender_id' => $data['sender_id'],
            'body' => $data['message'],
            'status' => 'pending',
            'metadata' => [
                'ip' => $request->ip(),
                'ua' => $request->header('User-Agent'),
            ],
        ]);

        // dispatch background job to 'messages' queue
        ProcessMessageJob::dispatch($message->id)->onQueue('messages');

        return response()->json(['id' => $message->id], 201);
    }
}
