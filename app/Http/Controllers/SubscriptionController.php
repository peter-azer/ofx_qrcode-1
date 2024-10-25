<?php


namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use App\Models\Package;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    // Post a new subscription
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'package_id' => 'required|exists:packages,id',
            'duration' => 'required|string|in:month,three_months,year',
           
        ]);

        // Check if the user is already subscribed to a package
        $existingSubscription = Subscription::where('user_id', $validatedData['user_id'])->where('is_enable', true)->first();

        if ($existingSubscription) {
            return response()->json(['message' => 'User is already subscribed to a package'], 400);
        }

        // Set start and end dates
        $startDate = Carbon::now();
        $endDate = Subscription::calculateEndDate(clone $startDate, $validatedData['duration']);

        // Create the subscription
        $subscription = Subscription::create([
            'user_id' => $validatedData['user_id'],
            'package_id' => $validatedData['package_id'],
            'start_date' => $startDate,
            'end_date' => $endDate,

            'duration' => $validatedData['duration'],
        ]);

        return response()->json(['message' => 'Subscription created successfully', 'data' => $subscription], 201);
    }

    // Get subscriptions by user ID
    public function getByUserId($userId)
    {
        $subscriptions = Subscription::where('user_id', $userId)->get();

        if ($subscriptions->isEmpty()) {
            return response()->json(['message' => 'No subscriptions found for this user'], 404);
        }

        return response()->json($subscriptions);
    }

    // Get   for admin subscriptions by package ID
    public function getByPackageId($packageId)
    {
        $subscriptions = Subscription::where('package_id', $packageId)->get();

        if ($subscriptions->isEmpty()) {
            return response()->json(['message' => 'No subscriptions found for this package'], 404);
        }

        return response()->json($subscriptions);
    }

    // Validate and disable subscriptions if the end date has passed
    public function validateSubscriptions()
    {
        $expiredSubscriptions = Subscription::where('end_date', '<', Carbon::now())->where('is_enable', true)->get();

        foreach ($expiredSubscriptions as $subscription) {
            $subscription->update(['is_enable' => false]);
        }

        return response()->json(['message' => 'Expired subscriptions have been disabled'], 200);
    }
}
