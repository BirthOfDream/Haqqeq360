<?php 

namespace App\Http\Controllers\Api\SubscriptionController;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:plans,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'category' => 'required|in:bootcamp,courses,workshops,programs',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'receipt' => 'required|file|mimes:pdf|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if user is authenticated
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please login first.',
            ], 401);
        }

        // Store receipt
        $receiptPath = $request->file('receipt')->store('receipts', 'public');

        // Create subscription with authenticated user
        $subscription = Subscription::create([
            'user_id' => auth()->id(), // Automatically uses authenticated user
            'plan_id' => $request->plan_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'category' => $request->category,
            'title' => $request->title,
            'amount' => $request->amount,
            'receipt_path' => $receiptPath,
            'status' => 'pending',
        ]);

        // Load relationships for response
        $subscription->load('plan.planable', 'user');

        return response()->json([
            'success' => true,
            'message' => 'Subscription submitted successfully. Awaiting admin approval.',
            'data' => [
                'id' => $subscription->id,
                'user' => [
                    'id' => $subscription->user->id,
                    'name' => $subscription->user->name,
                    'email' => $subscription->user->email,
                ],
                'plan' => [
                    'id' => $subscription->plan->id,
                    'name' => $subscription->plan->name,
                    'price' => $subscription->plan->price,
                    'type' => $subscription->plan->plan_type,
                ],
                'subscription_details' => [
                    'name' => $subscription->name,
                    'email' => $subscription->email,
                    'phone' => $subscription->phone,
                    'category' => $subscription->category,
                    'title' => $subscription->title,
                    'amount' => $subscription->amount,
                    'status' => $subscription->status,
                ],
                'receipt_path' => $subscription->receipt_path,
                'submitted_at' => $subscription->created_at->toDateTimeString(),
            ],
        ], 201);
    }

    public function index(): JsonResponse
    {
        $subscriptions = Subscription::where('user_id', auth()->id())
            ->with('plan.planable')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subscriptions,
        ]);
    }

    public function show(Subscription $subscription): JsonResponse
    {
        if ($subscription->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $subscription->load('plan.planable');

        return response()->json([
            'success' => true,
            'data' => $subscription,
        ]);
    }
}