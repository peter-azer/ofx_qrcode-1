<?php

namespace App\Http\Controllers;

use App\Models\QrCodeModel;
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
            'links' => 'nullable|array',
            'links.*' => 'url',
            'images' => 'nullable|array',
            'images.*' => 'file|mimes:jpeg,png,jpg',
            'mp3' => 'nullable|array',
            'mp3.*' => 'file|mimes:mp3',
            'pdfs' => 'nullable|array',
            'pdfs.*' => 'file|mimes:pdf',
            'event_date' => 'nullable',
            'event_time' => 'nullable',
            'location' => 'nullable|string',
        ]);
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

        // $profile = profile::create($validatedData);
        if (!empty($validatedData['links'])) {
            foreach ($validatedData['links'] as $link) {
                Links::create(['profile_id' => $profile->id, 'url' => $link]);
            }
        }


        if ($request->has('mp3')) {
            foreach ($request->file('mp3') as $mp3) {
                $mp3path = $mp3->store('records', 'public');
                images::create(['profile_id' => $profile->id, 'mp3_path' => $mp3path]);
            }
        }



        if ($request->has('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath = $image->store('images', 'public');
                images::create(['profile_id' => $profile->id, 'image_path' => $imagePath]);
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
        $qrCodeLink = 'https://qrcode.com/' . $uniqueName; // Replace with your custom domain


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
        $qrCode->package_id = $validatedData['package_id'];
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
public function getQRCodeByUserId($user_id)
{
    // Fetch all QR codes for the given user ID
    $qrCodes = QRCode::whereHas('profile', function($query) use ($user_id) {
        $query->where('user_id', $user_id);
    })->get(['id', 'qr_code', 'scan_count']);

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


}

