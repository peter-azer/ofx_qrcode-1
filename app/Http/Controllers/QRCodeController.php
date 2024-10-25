<?php

namespace App\Http\Controllers;
use App\Models\UserLocation;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use App\Models\QrCodeModel;
use Stevebauman\Location\Facades\Location;
use Illuminate\Support\Facades\Log;
class QRCodeController extends Controller
{



////////generate qr code for links direct

public function generateQrCode(Request $request)
{
    // Validate incoming data
    $validatedData = $request->validate([
        'link' => 'required|url',
        'package_id' => 'required',
    ]);

    $user = $request->user();
    $link = $validatedData['link'];

    // Create new QR Code Model entry
    $qrCodeModel = new QrCodeModel();
    $qrCodeModel->link = $link;
    $qrCodeModel->type = 'link';
    $qrCodeModel->profile_id = null;
    $qrCodeModel->user_id = $user->id;
    $qrCodeModel->package_id = $validatedData['package_id'];
    $qrCodeModel->save(); // Save model to generate ID

    // Generate a unique name for the QR code
    $uniqueName = uniqid();


    $fileName = 'qrcodes/' . $uniqueName . '.png';


    $qrCode = QrCode::format('png')
        ->backgroundColor(255, 255, 255)
        ->size(200)
        ->color(0, 0, 0)
        ->generate(route('qrcode.scan', ['name' => $uniqueName]));

    // Store the QR code file
    Storage::disk('public')->put($fileName, $qrCode);

    // Update the model with the file path
    $qrCodeModel->qrcode = $fileName;
    $qrCodeModel->save(); // Save again after storing the file

    // Generate the tracking link using the unique name
    $trackingLink = route('qrcode.scan', ['name' => $uniqueName]);

    // Return the QR code URL and the tracking link
    return response()->json([
        'qr_code_url' => Storage::url($fileName),
        'tracking_link' => $trackingLink,
    ]);
}




    // QrCodeController.php
public function trackAndRedirect($name,Request $request)
{
    $qrCodeModel = QrCodeModel::where('qrcode', 'like', '%/' . $name . '.png')->firstOrFail();
    ;

    if ($qrCodeModel->checkVisitorCount($qrCodeModel->scan_count,$qrCodeModel->package_id)) {
        // Increment the scan count only if within limits
        $qrCodeModel->scan_count += 1; // Increment scan count
        $qrCodeModel->save(); // Save the incremented scan count


        return redirect($qrCodeModel->link);
    } else {

        $qrCodeModel->is_active = 0;
        $qrCodeModel->save();
        abort(404);
    }

    // Increment the scan count
    // $qrCodeModel->scan_count += 1; // Simplified increment


    // $userLocation = Location::get($request->ip());
    // if ($userLocation) {


    //     if ($userLocation) {
    //         // Store the user's location in the user_location table
    //         $locationData = [
    //             'ip' => $userLocation->ip ?? 'N/A',
    //             'country' => $userLocation->countryName ?? 'N/A',
    //             'city' => $userLocation->cityName ?? 'N/A',
    //             'latitude' => $userLocation->latitude ?? null,
    //             'longitude' => $userLocation->longitude ?? null,
    //         ];

            // Save location data to user_location table
        // UserLocation::create([
        //          // Get the authenticated user ID
        //         'qrcode_id' => $qrCodeModel->id,// Link to the QR code
        //          'location' => null ,
        //         // 'location' => json_encode($locationData), // Store location as JSON
        //     ]);

            // Log the location data for debugging
            // Log::info('User Location saved:', $locationData);
        //}



//    }
}



    public function trackQRCode($id, Request $request)
    {
        // Find the QR code by ID
        $qrCodeModel = QrCodeModel::findOrFail($id);

        // Increment the scan count
        $qrCodeModel->scans_count += 1;
        $qrCodeModel->save();

        // Get the user's IP address
        $ipAddress = $request->ip();

        // Use a location service to get the user's location based on the IP
        $userLocation = Location::get($ipAddress);

        if ($userLocation) {
            // Check if userLocation has the expected properties
            if (isset($userLocation->ip) && isset($userLocation->countryName) && isset($userLocation->cityName)) {
                // Prepare location data array
                $locationData = [
                    'ip' => $userLocation->ip,
                    'country' => $userLocation->countryName,
                    'city' => $userLocation->cityName,
                    'latitude' => $userLocation->latitude,
                    'longitude' => $userLocation->longitude,
                ];

                // Convert location data to JSON format
                $locationJson = json_encode($locationData);

                // Save location data to the qr_code_model
                $qrCodeModel->user_location = $locationJson;
                $qrCodeModel->save();

                // Save location data to the user_location table
                UserLocation::create([
                    'user_id' => auth()->id(), // Assuming the user is authenticated
                    'qrcode_id' => $qrCodeModel->id, // Reference to the QR code
                    'location' => $locationJson, // Store location as JSON
                ]);

                // Log the location data
                Log::info('User Location saved:', $locationData);
            }
        }

        // Return a response with scan count, location, and IP
        return response()->json([
            'message' => 'QR code scan tracked successfully.',
            'qr_code_id' => $qrCodeModel->id,
            'scan_count' => $qrCodeModel->scans_count,
            'location' => $userLocation ? $locationJson : 'Location not found',
            'ip_address' => $ipAddress,
        ], 200);
    }






    // public function checkVisitorCount( $id)
    // {
    //     $qrcode = QrCodeModel::findOrFail($id);

    //     // Check and deactivate if conditions are met
    //     if ($qrcode->checkVisitorCount()) {
    //         return response()->json(['message' => 'QR code deactivated due to maximum visitor count reached'], 200);
    //     }

    //     return response()->json(['message' => 'QR code is still active'], 200);
    // }










}
