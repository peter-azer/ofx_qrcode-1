<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\QrCodeModel;
use Illuminate\Http\Request;
use App\Models\branches;
use App\Models\records;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use App\Models\events;
use App\Models\links;
use App\Models\images;
use App\Models\pdfs;
use App\Models\User;
use Illuminate\Support\Facades\Log;

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
        $profile = Profile::with(['links', 'images', 'pdfs', 'events', 'branches','records'])->find($id);

        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        return response()->json($profile, 200);
    }




    public function getProfileByQRCodeNamev2($qrCodeName)
    {
        // Fetch the QRCode entry by the unique QR code name
        $qrCode = QrCodeModel::where('link', 'https://ofx-qrcode.com/' . $qrCodeName)->first();

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
        $qrCode = QrCodeModel::where('link', 'https://ofx-qrcode.com/qr/' . $qrCodeName)->first();



        if ($qrCode->is_active == 0) {
            abort(404, 'QR code not found or is inactive.');
        }

        // Check if QR code exists
        if (!$qrCode) {
            return response()->json(['message' => 'QR code not found'], 404);
        }

        // Extract profile ID from QR code
        $profileId = $qrCode->profile_id;

        // Fetch the associated profile details using the profile ID
        $profile = Profile::with(['links', 'images', 'pdfs', 'events', 'records', 'branches'])->find($profileId);

        // Check if profile exists
        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        return response()->json($profile, 200);
    }

    public function updateProfile(Request $request, $id)
    {
        $validatedData = $request->validate([
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'phones' => 'nullable|array',
            'logo' => 'nullable|file',
            'cover' => 'nullable|file',
            'color' => 'nullable|string',
            'font' => 'nullable|string',
            'links' => 'nullable|array',
            'links.*.id' => 'nullable|integer|exists:links,id', // Ensure the link IDs exist for updates
            'links.*.url' => 'nullable|string',
            'links.*.type' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'nullable|file',
            'mp3' => 'nullable|array',
            'mp3.*' => 'nullable|file',
            'pdfs' => 'nullable|array',
            'pdfs.*' => 'nullable|file',
            'type.*' => 'nullable|string',
            'event_date' => 'nullable|date',
            'event_time' => 'nullable|string',
            'location' => 'nullable|string',
            'branches' => 'nullable|array',
            'branches.*.id' => 'nullable|integer|exists:branches,id', // Ensure branch IDs exist for updates
            'branches.*.name' => 'nullable|string',
            'branches.*.location' => 'nullable|string',
            'branches.*.phones' => 'nullable|array',
        ]);

        // Find the profile by ID
        $profile = Profile::findOrFail($id);

        // Update main profile details
        $profile->update([
            'title' => $validatedData['title'] ?? $profile->title,
            'description' => $validatedData['description'] ?? $profile->description,
            'phones' => $validatedData['phones'] ?? $profile->phones,
            'background_color' => $validatedData['color'] ?? $profile->background_color,
            'font' => $validatedData['font'] ?? $profile->font,
            'logo' => $request->file('logo') ? $request->file('logo')->store('logos', 'public') : $profile->logo,
            'cover' => $request->file('cover') ? $request->file('cover')->store('covers', 'public') : $profile->cover,
        ]);


        /** -------------------- LINKS -------------------- */
        if (!empty($validatedData['links'])) {
            foreach ($validatedData['links'] as $linkData) {
                Links::updateOrCreate(
                    ['id' => $linkData['id'] ?? null], // If ID exists, update; else create new
                    [
                        'profile_id' => $profile->id,
                        'url' => $linkData['url'] ?? '',
                        'type' => $linkData['type'] ?? '',
                    ]
                );
            }
        }


        // Update branches
        if (!empty($validatedData['branches'])) {
            foreach ($validatedData['branches'] as $branchData) {
                if (!empty($branchData['id'])) {
                    // Update existing branch
                    $branch = branches::findOrFail($branchData['id']);
                    $branch->update([
                        'name' => $branchData['name'],
                        'location' => $branchData['location'],
                        'phones' => $branchData['phones'],
                    ]);
                } else {
                    // Create new branch
                    branches::create([
                        'profile_id' => $profile->id,
                        'name' => $branchData['name'],
                        'location' => $branchData['location'],
                        'phones' => $branchData['phones'],
                    ]);
                }
            }
        }

        if ($request->has('mp3')) {
            foreach ($request->file('mp3') as $mp3) {
                $mp3path = $mp3->store('records', 'public');
                // log::info(' Data: ', $request);


                if ($request->has('mp3_id')) {
                    $record = Records::find($request->input('mp3_id'));
                    // log::info(' Data: ', $record);
                    if ($record) {
                        $record->update(['mp3_path' => $mp3path]);
                    }
                } else {
                    // Create new record if no mp3_id provided
                    Records::create(['profile_id' => $profile->id, 'mp3_path' => $mp3path]);
                }
            }
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                if ($image->isValid()) {
                    $imagePath = $image->store('images', 'public');

                    // If image_id is provided, update the record
                    if ($request->has('image_id')) {
                        $imageRecord = Images::find($request->input('image_id'));
                        if ($imageRecord) {
                            $imageRecord->update(['image_path' => $imagePath]);
                        }
                    } else {
                        // Create new record if no image_id provided
                        Images::create(['profile_id' => $profile->id, 'image_path' => $imagePath]);
                    }
                }
            }
        }

        if ($request->hasFile('pdfs')) {
            foreach ($request->file('pdfs') as $key => $pdf) {
                if ($pdf->isValid()) {
                    $type = $request->input("type.{$key}");
                    $pdfpath = $pdf->store('pdfs', 'public');

                    // If pdf_id is provided, update the record
                    if ($request->has('pdf_id')) {
                        $pdfRecord = Pdfs::find($request->input('pdf_id'));
                        if ($pdfRecord) {
                            $pdfRecord->update(['pdf_path' => $pdfpath, 'type' => $type]);
                        }
                    } else {
                        // Create new record if no pdf_id provided
                        Pdfs::create([
                            'profile_id' => $profile->id,
                            'pdf_path' => $pdfpath,
                            'type' => $type,
                        ]);
                    }
                }
            }
        }

        $profile = Profile::with(['records', 'images', 'pdfs'])->find($profile->id);

        // Return response with updated profile and related data
        return response()->json([
            'message' => 'Profile updated successfully',
            'profile' => $profile, // This includes the updated profile and related records
        ]);
    }


}
