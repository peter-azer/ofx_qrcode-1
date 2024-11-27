<?php

namespace App\Http\Controllers;

use App\Models\UserLocation;
use Illuminate\Http\Request;

class UserLocationController extends Controller
{
    public function getUserLocationByUserIdAndQRCodeId(Request $request, $userId, $qrCodeId)
    {
        // Validate request parameters
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'qrcode_id' => 'required|exists:qrcodes,id'
        ]);

        // Fetch user location data for the specified user and QR code
        $userLocations = UserLocation::where('user_id', $userId)
            ->where('qrcode_id', $qrCodeId)
            ->get();

        // If no location found, return a 404 response
        if ($userLocations->isEmpty()) {
            return response()->json(['message' => 'No location data found'], 404);
        }

        return response()->json($userLocations, 200);
    }


    // api for update any of this data may be all may be not 
}
