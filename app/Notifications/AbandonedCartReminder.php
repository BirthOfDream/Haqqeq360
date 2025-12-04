<?php

namespace App\Notifications;

use App\Models\AbandonedCart;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AbandonedCartReminder extends Notification
{
    use Queueable;

    public function __construct(public AbandonedCart $cart) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('لا تنسَ إكمال طلبك!')
            ->greeting('مرحباً ' . $notifiable->name)
            ->line('لاحظنا أنك تركت منتجاً في سلة التسوق.')
            ->line('المنتج: ' . $this->cart->product->name)
            ->action('إكمال الشراء', url('/checkout/' . $this->cart->id))
            ->line('لا تفوت هذه الفرصة!')
            ->salutation('مع تحياتنا');
    }

    public function toArray($notifiable): array
    {
        return [
            'cart_id' => $this->cart->id,
            'product_name' => $this->cart->product->name,
        ];
    }
}