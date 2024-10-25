<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\QrCodeModel;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    // Get all profiles by user_id
    public function getAllProfilesByUserId($user_id)
    {
        // Fetch all profiles associated with the user_id
        $profiles = Profile::where('user_id', $user_id)->get();

        if ($profiles->isEmpty()) {
            return response()->json(['message' => 'No profiles found for this user'], 404);
        }

        return response()->json($profiles, 200);
    }

    // Get a specific profile and its details by profile ID
    public function getProfileById($id)
    {
        // Find the profile by ID and include related data
        $profile = Profile::with(['links', 'images', 'pdfs', 'events'])->find($id);

        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        return response()->json($profile, 200);
    }


    public function getProfileByQRCodeNamev2($qrCodeName)
    {
        // Fetch the QRCode entry by the unique QR code name
        $qrCode = QrCodeModel::where('link', 'https://qrcode.com/' . $qrCodeName)->first();

        // Check if QR code exists
        if (!$qrCode) {
            return response()->json(['message' => 'QR code not found'], 404);
        }

        // Fetch the associated profile details
        $profile = $qrCode->profile()->with(['links', 'images', 'pdfs', 'events'])->first();

        // Check if profile exists
        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        return response()->json($profile, 200);
    }


    public function getProfileByQRCodeName($qrCodeName)
    {
        // Fetch the QRCode entry by the unique QR code name
        $qrCode = QrCodeModel::where('link', 'https://qrcode.com/' . $qrCodeName)->first();

        // Check if QR code exists
        if (!$qrCode) {
            return response()->json(['message' => 'QR code not found'], 404);
        }

        // Extract profile ID from QR code
        $profileId = $qrCode->profile_id;

        // Fetch the associated profile details using the profile ID
        $profile = Profile::with(['links', 'images', 'pdfs', 'events'])->find($profileId);

        // Check if profile exists
        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        return response()->json($profile, 200);
    }
}

