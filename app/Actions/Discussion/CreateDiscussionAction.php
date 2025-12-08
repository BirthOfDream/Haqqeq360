<?php

namespace App\Actions\Discussion;

use App\Models\Discussion;
use Illuminate\Support\Facades\Auth;

class CreateDiscussionAction
{
    /**
     * Execute the action.
     *
     * @param array $data
     * @return Discussion
     */
    public function execute(array $data): Discussion
    {
        // لو المستخدم حالياً موجود
        $userId = Auth::id();

        // لو Filament خزنت الصورة مسبقاً، data['image'] عبارة عن path string
        $imagePath = $data['image'] ?? null;

        $discussion = Discussion::create([
            'title'        => $data['title'],
            'content'      => $data['content'],
            'image'        => $imagePath,
            'published_at' => $data['published_at'] ?? now(),
            'is_published' => $data['is_published'] ?? false,
            'user_id'      => $userId,
        ]);

        return $discussion;
    }
}
