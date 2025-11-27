<?php

namespace App\Http\Controllers\Api\SubscriberController;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use App\Mail\SubscriptionConfirmed;
use Illuminate\Support\Facades\Mail;

class SubscriberController extends Controller
{
    /**
     * Store a new newsletter subscription.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'email' => 'required|email|unique:news_subscriptions,email'
    ]);

    $subscriber = Subscriber::create($validated);

    // Send confirmation email
    Mail::to($subscriber->email)->send(new SubscriptionConfirmed($subscriber->email));

    return response()->json([
        'success' => true,
        'message' => 'Subscribed successfully. Confirmation email sent.',
        'data' => $subscriber
    ], 201);
}
}
