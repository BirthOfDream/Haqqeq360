<?php

namespace App\Services;

use App\Models\User;
use App\Models\DiscussionThread;
use App\Models\DiscussionReply;
use App\Models\DiscussionNotification;
use App\Notifications\NewReplyNotification;
use Illuminate\Support\Facades\Log;

class DiscussionService
{
    /**
     * فلتر المحتوى المسيء
     */
    public function containsOffensiveContent(string $content): bool
    {
        // قائمة الكلمات المسيئة - يمكن تحسينها بقاعدة بيانات أو API خارجية
        $offensiveWords = [
            'كلمة1', 'كلمة2', 'كلمة3',
            // أضف المزيد حسب الحاجة
        ];

        $content = mb_strtolower($content);

        foreach ($offensiveWords as $word) {
            if (mb_strpos($content, $word) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * إنشاء رد جديد
     */
    public function createReply(
        DiscussionThread $thread,
        User $user,
        string $content,
        ?int $parentId = null
    ): DiscussionReply {
        // التحقق من إغلاق النقاش
        if ($thread->is_closed) {
            throw new \Exception('تم إغلاق الموضوع من قبل المدرب');
        }

        // فلتر المحتوى
        $isOffensive = $this->containsOffensiveContent($content);

        $reply = DiscussionReply::create([
            'thread_id' => $thread->id,
            'user_id' => $user->id,
            'parent_id' => $parentId,
            'content' => $content,
            'is_moderated' => $isOffensive,
            'moderation_reason' => $isOffensive ? 'محتوى مسيء - يحتاج مراجعة' : null,
        ]);

        // إرسال إشعارات
        if (!$isOffensive) {
            $this->sendReplyNotifications($reply);
        }

        return $reply;
    }

    /**
     * إرسال إشعارات الرد الجديد
     */
    public function sendReplyNotifications(DiscussionReply $reply): void
    {
        $thread = $reply->thread;
        $usersToNotify = collect();

        // إشعار صاحب الموضوع
        if ($thread->user_id !== $reply->user_id) {
            $usersToNotify->push($thread->user);
        }

        // إشعار صاحب الرد الأصلي (إذا كان رد على رد)
        if ($reply->parent_id) {
            $parentReply = $reply->parent;
            if ($parentReply->user_id !== $reply->user_id) {
                $usersToNotify->push($parentReply->user);
            }
        }

        // إشعار جميع المشاركين في النقاش
        $participants = DiscussionReply::where('thread_id', $thread->id)
            ->where('user_id', '!=', $reply->user_id)
            ->distinct('user_id')
            ->with('user')
            ->get()
            ->pluck('user');

        $usersToNotify = $usersToNotify->merge($participants)->unique('id');

        // إنشاء الإشعارات
        foreach ($usersToNotify as $user) {
            $this->createNotification($user, $reply, 'new_reply');
        }
    }

    /**
     * إنشاء إشعار
     */
    protected function createNotification(User $user, DiscussionReply $reply, string $type): void
    {
        try {
            $notification = DiscussionNotification::create([
                'user_id' => $user->id,
                'thread_id' => $reply->thread_id,
                'reply_id' => $reply->id,
                'type' => $type,
                'message' => $this->getNotificationMessage($reply, $type),
            ]);

            // محاولة إرسال البريد الإلكتروني
            try {
                $user->notify(new NewReplyNotification($reply));
                $notification->markEmailSent();
            } catch (\Exception $e) {
                // تسجيل الخطأ وعدم منع العملية الأساسية
                Log::error('Failed to send email notification', [
                    'notification_id' => $notification->id,
                    'error' => $e->getMessage(),
                ]);
            }
        } catch (\Exception $e) {
            // تسجيل الخطأ وعدم منع العملية الأساسية
            Log::error('Failed to create notification', [
                'user_id' => $user->id,
                'reply_id' => $reply->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * الحصول على رسالة الإشعار
     */
    protected function getNotificationMessage(DiscussionReply $reply, string $type): string
    {
        $userName = $reply->user->name;
        $threadTitle = $reply->thread->title;

        return match ($type) {
            'new_reply' => "{$userName} أضاف رد جديد على موضوع: {$threadTitle}",
            'new_like' => "{$userName} أعجب بردك في موضوع: {$threadTitle}",
            'thread_closed' => "تم إغلاق موضوع: {$threadTitle}",
            default => "إشعار جديد في موضوع: {$threadTitle}",
        };
    }

    /**
     * إضافة أو إزالة إعجاب
     */
    public function toggleLike($likeable, User $user): bool
    {
        $existingLike = $likeable->likes()->where('user_id', $user->id)->first();

        if ($existingLike) {
            $existingLike->delete();
            return false; // تم إزالة الإعجاب
        }

        $likeable->likes()->create([
            'user_id' => $user->id,
        ]);

        // إشعار صاحب المحتوى
        if ($likeable->user_id !== $user->id && $likeable instanceof DiscussionReply) {
            try {
                DiscussionNotification::create([
                    'user_id' => $likeable->user_id,
                    'thread_id' => $likeable->thread_id,
                    'reply_id' => $likeable->id,
                    'type' => 'new_like',
                    'message' => $this->getNotificationMessage($likeable, 'new_like'),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create like notification', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return true; // تم إضافة الإعجاب
    }

    /**
     * حذف رد (soft delete)
     */
    public function deleteReply(DiscussionReply $reply, User $user): void
    {
        // التحقق من الصلاحيات (المدرب أو صاحب الرد)
        $thread = $reply->thread;
        
        if ($user->id !== $reply->user_id && $user->id !== $thread->user_id) {
            throw new \Exception('غير مصرح لك بحذف هذا الرد');
        }

        $reply->delete();
    }

    /**
     * تعديل رد
     */
    public function updateReply(DiscussionReply $reply, User $user, string $content): DiscussionReply
    {
        // التحقق من الصلاحيات
        if ($user->id !== $reply->user_id) {
            throw new \Exception('غير مصرح لك بتعديل هذا الرد');
        }

        // فلتر المحتوى
        $isOffensive = $this->containsOffensiveContent($content);

        $reply->update([
            'content' => $content,
            'is_moderated' => $isOffensive,
            'moderation_reason' => $isOffensive ? 'محتوى مسيء - يحتاج مراجعة' : null,
        ]);

        return $reply->fresh();
    }
}