<?php


namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use App\Models\Package;
use App\Models\QrCodeModel;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Type\Decimal;

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

        $user = $request->user();

        // Retrieve the package being subscribed to
        $package = Package::findOrFail($validatedData['package_id']);
        $qrcodeLimit = $package->max_qrcode ?? 0;

        // Calculate the start and end dates based on the subscription duration
        $startDate = Carbon::now();
        $endDate = $this->calculateEndDate(clone $startDate, $validatedData['duration']);

        // Check if the user already has an active package
        $existingPackage = $user->packages()->first();

        if ($existingPackage) {
            // Detach the existing package
            // $user->packages()->detach($existingPackage->id);

            // Attach the new package with updated data
            $user->packages()->attach($validatedData['package_id'], [
                'duration' => $validatedData['duration'],
                'qrcode_limit' => $qrcodeLimit,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
        } else {
            // Attach a new package
            $user->packages()->attach($validatedData['package_id'], [
                'duration' => $validatedData['duration'],
                'qrcode_limit' => $qrcodeLimit,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
        }

        // Return a success message with the new package details
        return response()->json([
            'message' => $existingPackage ? 'Package updated successfully.' : 'Package subscribed successfully.',
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






    public function updateQrCodeLimit(Request $request)
    {
        $user = $request->user();

        $validatedData = $request->validate([
            'qrcode_limit' => 'required|integer|min:0', // Ensure it's a positive integer
        ]);

        // Get the user's package (package_id = 3)
        $userPackage = $user->packages()->where('user_id', $user->id)->first();
        // \Log::info('User Package:', ['user_package' => $userPackage]);
        if (!$userPackage) {
            return response()->json([
                'message' => 'User does not have Package 3.',
            ], 400);
        }

        // Update the qrcode_limit in the pivot table
        $newQrCodeLimit = $userPackage->pivot->qrcode_limit + $validatedData['qrcode_limit'];
        $userPackage->pivot->qrcode_limit = $newQrCodeLimit;
        $userPackage->pivot->is_enable = '1';
        $userPackage->pivot->save();
        QrCodeModel::where('user_id', $user->id)->update(['is_active' => 1]);
        return response()->json([
            'message' => 'QR code limit updated successfully.',
            'data' => [
                'user_id' => $user->id,
                'package_id' =>   $userPackage->pivot->package_id,
                'is_enable' => 1,
                'qrcode_limit' => $newQrCodeLimit,
            ],
        ], 200);
    }


    public function renewUserPackage(Request $request)
    {
        $user = $request->user();
    
        // Validate the request data
        $validatedData = $request->validate([
            'package_id' => 'required|integer|exists:packages,id',
            'duration' => 'required|string|in:year,month,3months',
        ]);
    
        // Find the new package by ID
        $newPackage = Package::find($validatedData['package_id']);
    
        if (!$newPackage) {
            return response()->json([
                'message' => 'The specified package does not exist.',
            ], 400);
        }
    
        // Calculate new duration dates
        $startDate = Carbon::now();
        $endDate = null;
    
        if ($validatedData['duration'] == 'year') {
            $endDate = $startDate->copy()->addYear();
        } elseif ($validatedData['duration'] == 'month') {
            $endDate = $startDate->copy()->addMonth();
        }
       elseif ($validatedData['duration'] == '3months') {
        $endDate = $startDate->copy()->addMonths(3);
    }
    
        // Retrieve the user's current package or attach the new one
        $userPackage = $user->packages()->where('user_id', $user->id)->first();
    
        if ($userPackage) {
            // Update the existing pivot entry and duration
            $user->packages()->updateExistingPivot($userPackage->id, [
                'package_id' => $newPackage->id,
                'qrcode_limit' => $newPackage->max_qrcode,
                'is_enable' => true,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'duration' => $validatedData['duration'],
            ]);
        } else {
            // Attach the new package with duration
            $user->packages()->attach($newPackage->id, [
                'qrcode_limit' => $newPackage->max_qrcode,
                'is_enable' => true,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'duration' => $validatedData['duration'],
            ]);
        }
    
        // Activate the user's QR codes
        QrCodeModel::where('user_id', $user->id)->update(['is_active' => 1]);
    
        return response()->json([
            'message' => 'User package renewed successfully.',
            'data' => [
                'user_id' => $user->id,
                'package_id' => $newPackage->id,
                'is_enable' => 1,
                'qrcode_limit' => $newPackage->max_qrcode,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
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

    public function validateUserSubscription(Request $request)
    {
        $user = $request->user();

        // Find an active package for the user
        $userPackage = $user->packages()
            // ->wherePivot('is_enable', '1')
            // ->wherePivot('end_date', '>', Carbon::now())
            ->first();

        // // Log the end_date if a package is found
        // if ($userPackage) {
        //     \Log::info('User subscription info:', ['end_date' => $userPackage->pivot->end_date]);
        // } else {
        //     \Log::info('No active subscription found for user.');
        // }

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
        $userPackage = $user->packages()->first();

        // If no active package is found
        if (!$userPackage) {
            return response()->json(['message' => 'Active subscription not found. You can activate a new subscription.'], 404);
        }

        // Get current package data
        $currentEndDate = Carbon::parse($userPackage->pivot->end_date)->startOfDay();
        $now = Carbon::now()->startOfDay();
        $isEnable = $userPackage->pivot->is_enable;

        if ($isEnable) {
            // If the subscription is enabled, check remaining days
            $remainingDays = $now->diffInDays($currentEndDate, false);

            if ($remainingDays > 1) {
                return response()->json(['message' => 'Your subscription is still active and cannot be renewed yet.'], 400);
            }
        } else {
            // If the subscription is disabled, allow updating duration
            return $this->updateDurationAndActivate($user, $userPackage, $validatedData['duration']);
        }

        // Update duration if the subscription is near expiration
        return $this->updateDurationAndActivate($user, $userPackage, $validatedData['duration']);
    }

    /**
     * Helper method to update the subscription duration and activate QR codes.
     */
    public function updateSubscriptionDurationv2(Request $request)
    {
        $user = $request->user();

        // Validate request input for duration
        $validatedData = $request->validate([
            'duration' => 'required|string|in:month,three_months,year',
        ]);

        // Find the active package for the user
        $userPackage = $user->packages()->first();

        // If no active package is found
        if (!$userPackage) {
            return response()->json(['message' => 'Active subscription not found. You can activate a new subscription.'], 404);
        }

        // Get current package data
        $currentEndDate = Carbon::parse($userPackage->pivot->end_date)->startOfDay();
        $now = Carbon::now()->startOfDay();
        $isEnable = $userPackage->pivot->is_enable;

        if ($isEnable) {

            $remainingDays = $now->diffInDays($currentEndDate, false);

            if ($remainingDays > 5) {
                return response()->json(['message' => 'Your subscription is still active and cannot be renewed yet.'], 400);
            }
        } else {

            return $this->updateDurationAndActivate($user, $userPackage, $validatedData['duration']);
        }


        return $this->updateDurationAndActivate($user, $userPackage, $validatedData['duration']);
    }


    public function price_upgrade(Request $request)
    {
        $user = $request->user();
        $userPackage = $user->packages()->first();

        if (!$userPackage) {
            return response()->json(['error' => 'No package found for this user.'], 404);
        }


        $priceCurrentPackage = $userPackage->price_EGP;
        $startDate = Carbon::parse($userPackage->pivot->start_date);
        $now = Carbon::now();


        $monthsUsed =  (int)$startDate->diffInMonths($now);
        $remainingMonths =  (int)12 - $monthsUsed; // Assuming a 12-month subscription


        $remainingValue = ($priceCurrentPackage / 12) * $remainingMonths;
        $remainingValueFormatted = number_format($remainingValue, 2);

        $packageId = $request->input('package_id');
        $newPackage = Package::find($packageId);

        if (!$newPackage) {
            return response()->json(['error' => 'Package not found.'], 404);
        }

        $newPackagePrice = $newPackage->price_EGP;


        $priceToPay = $newPackagePrice - $remainingValue;
        $priceToPayFormatted = number_format($priceToPay > 0 ? $priceToPay : 0, 2);
        return response()->json([
            // 'price' => $priceCurrentPackage,
            // 'months_used' => $monthsUsed,
            // 'remaining_months' => $remainingMonths,
            // 'remaining_value' => $remainingValueFormatted,
            'price_to_pay' => $priceToPayFormatted,
        ], 200);
    }


    /**
     * Calculate the new price based on the number of QR codes in the user's package.
     *
     * The price is based on the number of QR codes the user has beyond the default (2 QR codes).
     * For each additional set of 2 QR codes, 100 is added to the base package price.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */

     public function price_qr(Request $request)
     {
         // Validate the incoming request
         $validatedData = $request->validate([
             'price_qr' => 'required|numeric',
             'price_monthly' => 'nullable|numeric', 
         ]);
     
        
         $price = $validatedData['price_qr'];
         $priceMonthly = $validatedData['price_monthly'] ?? null; // Default to null if not provided
     
         
         $user = $request->user();
     
         if (!$user) {
             return response()->json(['error' => 'User not authenticated.'], 401);
         }
     
  
         $userPackage = $user->packages()->first();
     
         if (!$userPackage) {
             return response()->json(['error' => 'No package found for this user.'], 404);
         }
     
         
         $price_twoQR = $price; 
         $num_qr = $userPackage->max_visitor; 
         $package_price = $userPackage->price_EGP; 
         $user_qr = $userPackage->pivot->qrcode_limit;
     
         
         if ($num_qr == $user_qr) {
        
             $new_price = $priceMonthly ?? $package_price; 
         } else {
             $extra_qrs = $user_qr - 2; 
             $extra_sets = ceil($extra_qrs / 2); 
     
             if ($priceMonthly) {
          
                 $new_price = $priceMonthly + ($extra_sets * $price_twoQR);
             } else {
                
                 $new_price = $package_price + ($extra_sets * $price_twoQR);
             }
         }
     
         
         return response()->json([
             'new_price' => $new_price
         ], 200);
     }
     
    /**
     * Helper method to update the subscription duration and activate QR codes.
     */
    private function updateDurationAndActivate($user, $userPackage, $duration)
    {

        $startDate = Carbon::now();
        $endDate = $this->calculateEndDate($startDate, $duration);


        $user->packages()->updateExistingPivot($userPackage->id, [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration' => $duration,
            'is_enable' => true,
        ]);

        // Activate QR codes for the user
        QrCodeModel::where('user_id', $user->id)->update(['is_active' => 1]);

        return response()->json([
            'message' => 'Subscription duration updated successfully.',
            'data' => [
                'package_id' => $userPackage->id,
                'duration' => $duration,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        ], 200);
    }






    public function checkSubscriptionStatus(Request $request)
    {
        // Assume the user ID and package ID are passed in the request
        $user = auth()->user(); // Get authenticated user
        $userPackage = $user->packages()->first(); // Fetch the user's package

        if (!$userPackage) {
            return response()->json(['message' => 'Package not found for the user.'], 404);
        }

        // Check if the subscription is enabled
        if ($userPackage->pivot->is_enable == 0) {
            return response()->json(['message' => 'Your subscription has ended.'], 200);
        }

        // Get the subscription end date
        $currentEndDate = Carbon::parse($userPackage->pivot->end_date); // Assuming `end_date` exists
        $now = Carbon::now();

        // Calculate the remaining days
        $remainingDays = $now->diffInDays($currentEndDate, false);

        // Check if the subscription can be renewed
        if ($remainingDays > 5) {
            return response()->json(['message' => 'Your subscription is still active and cannot be renewed yet.'], 400);
        }

        // If remaining days are 1 or less, the user can renew
        return response()->json(['message' => 'You can renew your subscription now.'], 200);
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
}
