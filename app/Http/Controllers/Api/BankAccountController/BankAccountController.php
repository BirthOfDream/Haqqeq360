<?php

namespace App\Http\Controllers\Api\BankAccountController;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    /**
     * Get all active bank accounts
     */
    public function index(): JsonResponse
    {
        $bankAccounts = BankAccount::active()
            ->orderBy('bank_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bankAccounts,
        ]);
    }

    /**
     * Get a specific bank account
     */
    public function show(BankAccount $bankAccount): JsonResponse
    {
        if (!$bankAccount->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Bank account is not active',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $bankAccount,
        ]);
    }

    /**
     * Get bank accounts by bank name
     */
    public function getByBank(Request $request): JsonResponse
    {
        $bankName = $request->input('bank_name');

        $bankAccounts = BankAccount::active()
            ->where('bank_name', 'like', "%{$bankName}%")
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bankAccounts,
        ]);
    }
}