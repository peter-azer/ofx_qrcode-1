<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\branches;
use App\Models\QrCodeModel;
use App\Models\records;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use App\Models\Profile;
use App\Models\events;
use App\Models\links;
use App\Models\images;
use App\Models\pdfs;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
class qrcodev2Controller extends Controller
{





    public function saveProfileData(Request $request)
{
    try {
        // Ensure the user is authenticated
        $user = $request->user();
        $packageId = $request->input('package_id');


        $userPackage = $user->packages()->where('package_id', $packageId)->first();

        if (!$userPackage) {
            return response()->json([
                'message' => 'User does not have the selected package.',
            ], 400);
        }

        // Get the qrcode_limit from the user's package
        $qrcodeLimit = $userPackage->pivot->qrcode_limit;

        // Check the number of QR codes the user has for the selected package
        $userProfileCount = QrCodeModel::where('user_id', $user->id)
            ->where('package_id', $packageId)
            ->count();
     
        // If the user has reached the QR code limit for this package, return an error
        if ($userProfileCount >= $qrcodeLimit) {
            return response()->json([
                'message' => "You have reached the maximum profile limit of QR codes for this package.",
            ], 400);
        }
        // Validate request data
        $validatedData = $request->validate([
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'phones' => 'nullable|array',
            'logo' => 'nullable|file',
            'cover' => 'nullable|file',
            'color' => 'nullable|string',
            'font' => 'nullable|string',
            'package_id' => 'nullable|string',
            'links' => 'nullable|array',
            'links.*.url' => 'nullable|string',
            'links.*.type' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'nullable|file',
            'mp3' => 'nullable|array',
            'mp3.*' => 'nullable|file',
            'pdfs' => 'nullable|array',
            'pdfs.*' => 'nullable|file',
            'type.*' => 'nullable|string',
            'event_date' => 'nullable',
            'event_time' => 'nullable',
            'location' => 'nullable|string',
            'branches' => 'nullable|array',
            'branches.*.name' => 'nullable|string',
            'branches.*.location' => 'nullable|string',
            'branches.*.phones' => 'nullable|array',
        ]);

        // Initialize profile data
        $profile = Profile::create([
            'user_id' => $user->id,
            'logo' => $request->file('logo') ? $request->file('logo')->store('logos', 'public') : null,
            'phones' => $validatedData['phones'] ?? null,
            'cover' => $request->file('cover') ? $request->file('cover')->store('covers', 'public') : null,
            'background_color' => $validatedData['color'] ?? null,
            'title' => $validatedData['title'] ?? null,
            'description' => $validatedData['description'] ?? null,
            'font' => $validatedData['font'] ?? null,
        ]);

        // Queue relational data (batch inserts)
        $linksData = collect($validatedData['links'] ?? [])->map(function ($link) use ($profile) {
            return [
                'profile_id' => $profile->id,
                'url' => $link['url'],
                'type' => $link['type']
            ];
        })->toArray();
        links::insert($linksData);

        $branchesData = collect($validatedData['branches'] ?? [])->map(function ($branch) use ($profile) {
            return [
                'profile_id' => $profile->id,
                'name' => $branch['name'],
                'location' => $branch['location'],
                'phones' => isset($branch['phones']) ? json_encode($branch['phones']) : null, // Convert phones array to JSON
            ];
        })->toArray();
        branches::insert($branchesData);

        // Queue file uploads for background processing
        if ($request->hasFile('mp3')) {
            $mp3Files = [];
            foreach ($request->file('mp3') as $mp3) {
                $mp3Files[] = ['profile_id' => $profile->id, 'mp3_path' => $mp3->store('records', 'public')];
            }
            records::insert($mp3Files);
        }

        if ($request->hasFile('images')) {
            $imagesData = [];
            foreach ($request->file('images') as $image) {
                if ($image->isValid()) {
                    $imagesData[] = ['profile_id' => $profile->id, 'image_path' => $image->store('images', 'public')];
                }
            }
            images::insert($imagesData);
        }

        if ($request->hasFile('pdfs')) {
            $pdfsData = [];
            foreach ($request->file('pdfs') as $key => $pdf) {
                if ($pdf->isValid()) {
                    $type = $request->input("type.{$key}");
                    $pdfsData[] = [
                        'profile_id' => $profile->id,
                        'pdf_path' => $pdf->store('pdfs', 'public'),
                        'type' => $type
                    ];
                }
            }
            pdfs::insert($pdfsData);
        }

        // Insert event data
        if (!empty($validatedData['event_date'])) {
            events::create([
                'profile_id' => $profile->id,
                'event_date' => $validatedData['event_date'],
                'event_time' => $validatedData['event_time'],
                'location' => $validatedData['location']
            ]);
        }

        return response()->json(['message' => 'Profile data saved successfully', 'profile_id' => $profile->id], 201);

    } catch (\Exception $e) {
        return response()->json(['message' => 'Error saving profile data', 'error' => $e->getMessage()], 500);
    }
}


    public function generateQRCodeByProfileId(Request $request, $profileId)
    {
        try {
            $user = $request->user();
            $packageId = $request->input('package_id');




            $uniqueName = uniqid();
            $qrCodeLink = 'https://ofx-qrcode.com/qr/' . $uniqueName;

            $qrCodeData = QrCode::format('png')
                ->backgroundColor(255, 255, 255)
                ->size(200)
                ->color(0, 0, 0)
                ->generate($qrCodeLink);

            $fileName = 'qrcodes/' . uniqid() . '.png';
            Storage::disk('public')->put($fileName, $qrCodeData);

            $qrCode = QrCodeModel::create([
                'profile_id' => $profileId,
                'user_id' => $user->id,
                'qrcode' => $fileName,
                'link' => $qrCodeLink,
                'package_id' => $packageId,
                'scan_count' => 0,
                'is_active' => true,
            ]);

            return response()->json([
                'message' => 'QR code generated successfully',
                'qr_code' => $qrCode->qrcode,
                'link' => $qrCode->link
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Profile not found'], 404);
        }
    }

}
