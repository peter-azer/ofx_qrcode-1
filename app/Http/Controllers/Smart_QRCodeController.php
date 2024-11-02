<?php

namespace App\Http\Controllers;

use App\Models\branches;
use App\Models\QrCodeModel;
use App\Models\records;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Profile;
use App\Models\events;
use App\Models\links;
use App\Models\images;
use App\Models\pdfs;
use Illuminate\Validation\ValidationException;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
class Smart_QRCodeController extends Controller
{
    public function generatesmartQRCode(Request $request)
    {

        try {
        $user = $request->user();

        $validatedData = $request->validate([
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'phones' => 'nullable|array',
            'logo' => 'nullable|file|mimes:jpeg,png,jpg',
            'cover' => 'nullable|file|mimes:jpeg,png,jpg',
            'color' => 'nullable|string', // Hex code
            'font' => 'nullable|string',
            'package_id' => 'nullable|string',
            'links' => 'nullable|array', // links can be null or an array
            'links.*.url' => 'nullable|url', // Each url can be null or a valid URL
            'links.*.type' => 'nullable|string', // Ensure each link has a type

            'images' => 'nullable|array',
            'images.*' => 'file|mimes:jpeg,png,jpg',
            'mp3' => 'nullable|array',
            'mp3.*' => 'file|mimes:mp3',
            'pdfs' => 'nullable|array',
            'pdfs.*' => 'file|mimes:pdf',
            'event_date' => 'nullable',
            'event_time' => 'nullable',
            'location' => 'nullable|string',

         'branches' => 'nullable|array',
            'branches.*.name' => 'required|string',
            'branches.*.location' => 'required|string',
            'branches.*.phones' => 'nullable|array',
        ]);

        // Initialize an array to store uploaded image paths

        $profile = Profile::create([
            'user_id' => $user->id,
            'logo' => $request->file('logo') ? $request->file('logo')->store('logos', 'public') : null,
            'phones' => $validatedData['phones'] ?? null, // Convert array to JSON
            'cover' => $request->file('cover') ? $request->file('cover')->store('covers', 'public') : null,
            'background_color' => $validatedData['color'] ?? null,
            'title' => $validatedData['title'] ?? null,
            'description' => $validatedData['description'] ?? null,
            'font' => $validatedData['font'] ?? null,
        ]);

        if (!empty($validatedData['links'])) {
            foreach ($validatedData['links'] as $linkData) {
                // Check if url and type are present
                if (!empty($linkData['url']) && !empty($linkData['type'])) {
                    links::create([
                        'profile_id' => $profile->id,
                        'url' => $linkData['url'],
                        'type' => $linkData['type'], // Correct syntax for associative array
                    ]);
                }
            }
        }

        if (!empty($validatedData['branches'])) {
            foreach ($validatedData['branches'] as $branchData) {
                branches::create([
                    'profile_id' => $profile->id,
                    'name' => $branchData['name'],
                    'location' => $branchData['location'],
                    'phones' => $branchData['phones'] ?? null, // Convert array to JSON
                ]);
            }
        }
        if ($request->has('mp3')) {
            foreach ($request->file('mp3') as $mp3) {
                $mp3path = $mp3->store('records', 'public');
                records::create(['profile_id' => $profile->id, 'mp3_path' => $mp3path]);
            }
        }

    //    dd($request->file('images'));

    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            // Check if the file is valid before processing
            if ($image->isValid()) {
                $imagePath = $image->store('images', 'public');
                images::create([
                    'profile_id' => $profile->id,
                    'image_path' => $imagePath,
                ]);
            }
        }
    }




        if ($request->has('pdfs')) {
            foreach ($request->file('pdfs') as $pdf) {
                $pdfPath = $pdf->store('pdfs', 'public');
                Pdfs::create(['profile_id' => $profile->id, 'pdf_path' => $pdfPath]);
            }
        }

        if (!empty($validatedData['event_date'])) {
            events::create([
                'profile_id' => $profile->id,
                'event_date' => $validatedData['event_date'],
                'event_time' => $validatedData['event_time'],
                'location' => $validatedData['location']
            ]);
        }

        $uniqueName = uniqid();
        $qrCodeLink = 'https://ofx-qrcode.com/' . $uniqueName; // Replace with your custom domain


        $qrCodeData = QrCode::format('png')
            ->backgroundColor(255, 255, 255)
            ->size(200)
            ->color(0, 0, 0)
            ->generate($qrCodeLink);

        $fileName = 'qrcodes/' . uniqid() . '.png';
        Storage::disk('public')->put($fileName, $qrCodeData);

        $qrCode = new QrCodeModel();

        $qrCode->profile_id = $profile->id;
        $qrCode->user_id = $user->id;
        $qrCode->qrcode = $fileName;
        $qrCode->link = $qrCodeLink;
        $qrCode->package_id = $validatedData['package_id']?? null;
        $qrCode->scan_count = 0;
        $qrCode->is_active = true;
        $qrCode->save();

        return response()->json([
            'message' => 'QR code generated successfully',
            'qr_code' => $qrCode->qrcode,
            'link' => $qrCode->link
        ], 200);
    } catch (ValidationException $e) {
        return response()->json([
            'message' => 'Validation errors occurred.',
            'errors' => $e->validator->errors()
        ], 422);

    }}
//////
public function getQRCodesByUserId($user_id)
{
    // Fetch all QR codes for the given user ID
    $qrCodes = QrCodeModel::whereHas('profile', function($query) use ($user_id) {
        $query->where('user_id', $user_id);
    })->get();

    if ($qrCodes->isEmpty()) {
        return response()->json(['message' => 'No QR codes found for this user'], 404);
    }

    return response()->json($qrCodes, 200);
}

// Delete a QR code by qr_code ID
public function deleteQRCodeById($id)
{
    // Find the QR code by ID
    $qrCode = QRCode::find($id);

    if (!$qrCode) {
        return response()->json(['message' => 'QR code not found'], 404);
    }

    // Delete the QR code
    $qrCode->delete();

    return response()->json(['message' => 'QR code deleted successfully'], 200);
}



public function uploadImages(Request $request)
{
    try {

        $validatedData = $request->validate([
            'images' => 'required|array|min:1',
            'images.*' => 'file|mimes:jpeg,png,jpg',
        ]);
        \Log::info('Validated Request Data:', $validatedData);
        $uploadedImages = [];

        // Check if images were uploaded
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                // Store the image and save the path
                $imagePath = $image->store('images', 'public');

                // Save the image path in the database
                Images::create([
                    'profile_id' => '1', // Adjust to use dynamic profile ID if needed
                    'image_path' => $imagePath,
                ]);
                $uploadedImages[] = $imagePath;

                // Log the details of the uploaded image
                \Log::info('Uploaded Image ' . ($index + 1) . ':', [
                    'image_path' => $imagePath,
                    'original_name' => $image->getClientOriginalName(),
                    'mime_type' => $image->getClientMimeType(),   ]);

            }
        }



        return response()->json([
            'message' => 'Images uploaded successfully',
            'uploaded_images' => $uploadedImages,
        ], 200);

    } catch (\Exception $e) {
        // Log the error for debugging
        \Log::error('Error uploading images: ' . $e->getMessage());

        // Return error response
        return response()->json([
            'message' => 'Failed to upload images',
            'error' => $e->getMessage(),
        ], 500);
    }
}









}

