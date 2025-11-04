<?php

namespace App\Jobs;

use App\Models\User;
use App\Repositories\NotificationRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBulkNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $title;
    protected string $message;
    protected string $type;
    protected ?array $userIds;

    public function __construct(string $title, string $message, string $type = 'system', ?array $userIds = null)
    {
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
        $this->userIds = $userIds;
    }

    public function handle(NotificationRepository $notifications): void
    {
        try {
            // Determine recipients
            $query = User::query();
            if ($this->userIds) {
                $query->whereIn('id', $this->userIds);
            }
            $users = $query->pluck('id');

            // Chunk inserts for performance
            $chunks = $users->chunk(500);
            foreach ($chunks as $chunk) {
                $data = $chunk->map(fn($userId) => [
                    'user_id' => $userId,
                    'title' => $this->title,
                    'message' => $this->message,
                    'type' => $this->type,
                    'is_read' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->toArray();

                $notifications->bulkInsert($data);
            }

        } catch (\Throwable $e) {
            Log::error('Bulk notification job failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
