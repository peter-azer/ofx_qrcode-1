<?php

namespace App\Http\Controllers;

use App\Models\QrCodeModel;
use Illuminate\Http\Request;
use App\Models\Profile;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use App\Jobs\GenerateSmartQRCodeJob;
class smartqrcodev2Controller extends Controller
{


    public function generatesmartQRCodev3(Request $request)
    {
        try {
            $user = $request->user();
            $packageId = $request->input('package_id');
            $userPackage = $user->packages()->where('package_id', $packageId)->first();

            if (!$userPackage) {
                return response()->json([
                    'message' => 'User does not have the selected package.',
                ], 400);
            }


            $qrcodeLimit = $userPackage->pivot->qrcode_limit;


            $userProfileCount = QrCodeModel::where('user_id', $user->id)
                ->where('package_id', $packageId)
                ->count();


            if ($userProfileCount >= $qrcodeLimit) {
                return response()->json([
                    'message' => "You have reached the maximum profile limit of QR codes for this package.",
                ], 400);
            }

            // Validate the incoming request data
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

            // Create the profile (as done before)
            $profile = Profile::create([
                'user_id' =>  $user->id,
                'logo' => $request->file('logo') ? $request->file('logo')->store('logos', 'public') : null,
                'phones' => $validatedData['phones'] ?? null,
                'cover' => $request->file('cover') ? $request->file('cover')->store('covers', 'public') : null,
                'background_color' => $validatedData['color'] ?? null,
                'title' => $validatedData['title'] ?? null,
                'description' => $validatedData['description'] ?? null,
                'font' => $validatedData['font'] ?? null,
            ]);

            // Dispatch the job to handle QR code generation and associated tasks
            GenerateSmartQRCodeJob::dispatch($user, $validatedData, $profile);

            // Return a quick response
            return response()->json([
                'message' => 'QR code generation is in progress. You will be notified once it is done.',
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation errors occurred.',
                'errors' => $e->validator->errors()
            ], 422);
        }
    }

}
