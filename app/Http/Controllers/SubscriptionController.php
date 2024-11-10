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
        'package_id' => 'required|exists:packages,id',
        'duration' => 'required|string|in:month,three_months,year',
    ]);

    // Get the user ID from the authenticated user
    $user = $request->user();

    // Define the QR code limits for each package
    $qrCodeLimits = [
        1 => 10,   // Package 1 has a limit of 10 QR codes
        2 => 50,   // Package 2 has a limit of 50 QR codes
        3 => 100,  // Package 3 has a limit of 100 QR codes
    ];

    // Get the QR code limit for the selected package
    $qrcodeLimit = $qrCodeLimits[$validatedData['package_id']] ?? 0; // Default to 0 if no limit is found

    // Calculate the start and end dates based on the subscription duration
    $startDate = Carbon::now();
    $endDate = $this->calculateEndDate(clone $startDate, $validatedData['duration']);

    // Check if the user already has an active package in the 'user_packages' pivot table
    $existingPackage = $user->packages()->where('package_id', $validatedData['package_id'])->first();

    if ($existingPackage) {
        return response()->json(['message' => 'User is already subscribed to this package'], 400);
    }

    // Attach the new package to the user with the provided duration and QR code limit
    $user->packages()->attach($validatedData['package_id'], [
        'duration' => $validatedData['duration'],
        'qrcode_limit' => $qrcodeLimit,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);

    // Return a success message with the new package details
    return response()->json([
        'message' => 'Package subscribed successfully.',
        'data' => [
            'user_id' => $user->id,
            'package_id' => $validatedData['package_id'],
            'duration' => $validatedData['duration'],
            'qrcode_limit' => $qrcodeLimit,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]
    ], 201);
}




private function calculateEndDate($startDate, $duration)
{
    switch ($duration) {
        case 'month':
            return $startDate->copy()->addMonth();
        case 'three_months':
            return $startDate->copy()->addMonths(3);
        case 'year':
            return $startDate->copy()->addYear();
        default:
            throw new \InvalidArgumentException("Invalid duration: $duration");
    }

}



public function updateQrCodeLimit(Request $request)
{
    $user = $request->user();

    $validatedData = $request->validate([
        'qrcode_limit' => 'required|integer|min:0', // Ensure it's a positive integer
    ]);

    // Get the user's package (package_id = 3)
    $userPackage = $user->packages()->where('user_id' , 1)->first();
    // \Log::info('User Package:', ['user_package' => $userPackage]);
    if (!$userPackage) {
        return response()->json([
            'message' => 'User does not have Package 3.',
        ], 400);
    }

    // Update the qrcode_limit in the pivot table
    $userPackage->pivot->qrcode_limit = $validatedData['qrcode_limit'];
    $userPackage->pivot->is_enable='1';
    $userPackage->pivot->save();
   QrCodeModel::where('user_id', $user->id)->update(['is_active' => 1]);
    return response()->json([
        'message' => 'QR code limit updated successfully.',
        'data' => [
            'user_id' => $user->id,
            'package_id' => 3,
            'is_enable' => 1,
            'qrcode_limit' => $validatedData['qrcode_limit'],
        ],
    ], 200);
}


    // Get subscriptions by user ID
    public function getByUserId(Request $request)
    {
        // Retrieve the authenticated user
        $user = $request->user();
    
        // Access the packages related to the user through the many-to-many relationship
        $subscriptions = $user->packages()->get();
    
        // Check if the user has any subscriptions
        if ($subscriptions->isEmpty()) {
            return response()->json(['message' => 'No subscriptions found for this user'], 404);
        }
    
        // Return the subscriptions in JSON format
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

    public function validateUserSubscription(Request $request)
    {
        $user = $request->user();

        // Find an active package for the user
        $userPackage = $user->packages()
            ->wherePivot('is_enable', true)
            ->wherePivot('end_date', '>', Carbon::now())
            ->first();

        if (!$userPackage) {
            return response()->json(['message' => 'User not subscribed yet or subscription has expired.'], 404);
        }

        // Check if the package has expired
        if (Carbon::now()->greaterThan($userPackage->pivot->end_date)) {
            // Disable the package
            $user->packages()->updateExistingPivot($userPackage->id, ['is_enable' => false]);

            // Disable all QR codes related to this user
            QrCodeModel::where('user_id', $user->id)->update(['is_active' => 0]);

            return response()->json([
                'message' => 'Subscription has expired and has been disabled. All QR codes have been disabled.'
            ], 200);
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

        // Find the active package for the user
        $userPackage = $user->packages()->wherePivot('is_enable', true)->first();

        if (!$userPackage) {
            return response()->json(['message' => 'Active subscription not found'], 404);
        }

        // Set start date as today
        $startDate = Carbon::now();

        // Calculate the new end date based on the duration
        $endDate = $this->calculateEndDate($startDate, $validatedData['duration']);

        // Update the pivot table with the new start and end dates, duration, and enable the package if not enabled
        $user->packages()->updateExistingPivot($userPackage->id, [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration' => $validatedData['duration'],
            'is_enable' => true
        ]);

        return response()->json([
            'message' => 'Subscription duration updated successfully',
            'data' => [
                'package_id' => $userPackage->id,
                'duration' => $validatedData['duration'],
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        ], 200);
    }

}
