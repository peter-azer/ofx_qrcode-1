<?php


namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use App\Models\Package;
use App\Models\QrCodeModel;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    // Post a new subscription

    public function store(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            // 'user_id' => 'required|exists:users,id',
            'package_id' => 'required|exists:packages,id',
            'duration' => 'required|string|in:month,three_months,year',
        ]);
        // Get the user ID from the authenticated user
        $user = $request->user();
        // Check if the user already has an active subscription
        $existingSubscription = Subscription::where('user_id', $user->id)
            ->where('is_enable', true)
            ->first();

        if ($existingSubscription) {
            // If an active subscription exists, return an error message
            return response()->json(['message' => 'User is already subscribed to a package'], 400);
        }

        // Calculate the start and end dates based on the subscription duration
        $startDate = Carbon::now();
        $endDate = Subscription::calculateEndDate(clone $startDate, $validatedData['duration']);

        // Create the new subscription for the user
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'package_id' => $validatedData['package_id'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration' => $validatedData['duration'],
            'is_enable' => true, // Ensure this new subscription is active
        ]);

        // Return a success message with the subscription details
        return response()->json(['message' => 'Subscription created successfully', 'data' => $subscription], 201);
    }

    // Get subscriptions by user ID
    public function getByUserId(Request $request)
    {
        $user = $request->user();
        $subscriptions = Subscription::where('user_id', $user->id)->get();

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
    public function validateUserSubscription(Request $request)
    {
        $user = $request->user();
        $subscription = Subscription::where('user_id', $user->id)->where('is_enable', true)->first();

        if (!$subscription) {
            return response()->json(['message' => 'User not subscribed yet.'], 404);
        }

        if (Carbon::now()->greaterThan($subscription->end_date)) {
            // Disable the subscription
            $subscription->update(['is_enable' => false]);

            // Disable all QR codes related to this user
            QrCodeModel::where('user_id', $user->id)->update(['is_active' => 0]);

            return response()->json(['message' => 'Subscription has expired and has been disabled. All QR codes have been disabled.'], 200);
        }

        return response()->json(['message' => 'Subscription is still active.'], 200);
    }




    public function updateSubscriptionDuration(Request $request)
    {

        $user = $request->user();
        // Validate request input for duration
        $validatedData = $request->validate([
            'duration' => 'required|string|in:month,three_months,year',
        ]);

        // Find the subscription by ID
        $subscription = Subscription::where('user_id', $user->id)
            ->first();

        if (!$subscription) {
            return response()->json(['message' => 'Subscription not found'], 404);
        }

        // Update subscription to be enabled if it’s not already
        if (!$subscription->is_enable) {
            $subscription->is_enable = true;
        }

        // Update duration and calculate the new end date
        $subscription->duration = $validatedData['duration'];
        $subscription->end_date = Subscription::calculateEndDate(Carbon::now(), $validatedData['duration']);

        // Save the updated subscription
        $subscription->save();

        return response()->json(['message' => 'Subscription duration updated successfully', 'data' => $subscription], 200);
    }
}
