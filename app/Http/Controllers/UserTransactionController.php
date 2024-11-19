<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserTransaction;
class UserTransactionController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        $validatedData = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'transaction_status' => 'required|in:success,failure,pending',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string',
            'failure_reason' => 'nullable|string',
        ]);

        $transaction = UserTransaction::create([
            'user_id' => $user->id,
            'package_id' => $validatedData['package_id'],
            'transaction_status' => $validatedData['transaction_status'],
            'amount' => $validatedData['amount'],
            'payment_method' => $validatedData['payment_method'],
            'failure_reason' => $validatedData['failure_reason'],
        ]);

        return response()->json(['message' => 'Transaction created successfully', 'data' => $transaction], 201);
    }


    /**
 * Get all transactions.
 */
public function getAll()
{
    $transactions = UserTransaction::with(['user', 'package'])->get();

    return response()->json(['data' => $transactions], 200);
}


/**
 * Get transactions by status.
 */
public function getByStatus($status)
{
    $validStatuses = ['success', 'failure', 'pending'];

    if (!in_array($status, $validStatuses)) {
        return response()->json(['message' => 'Invalid status provided'], 400);
    }

    $transactions = UserTransaction::where('transaction_status', $status)
        ->with(['user', 'package'])
        ->get();

    return response()->json(['data' => $transactions], 200);
}


}
