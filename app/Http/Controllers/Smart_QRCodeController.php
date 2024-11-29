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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
// use PDF;

class Smart_QRCodeController extends Controller
{


    public function showForm()
    {
        return view('qrform'); // Refers to the HTML form view
    }
    public function generatesmartQRCodev2(Request $request)
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

        if (!empty($validatedData['links'])) {
            foreach ($validatedData['links'] as $linkData) {
                // Check if url and type are present
                if (!empty($linkData['url']) && !empty($linkData['type'])) {
                    links::create([
                        'profile_id' => $profile->id,
                        'url' => $linkData['url'],
                        'type' => $linkData['type'],
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


    if ($request->hasFile('pdfs')) {
        foreach ($request->file('pdfs') as $key => $pdf) {
            if ($pdf->isValid()) {
                // Get the type for each specific PDF
                $type = $request->input("type.{$key}");

                // Store the PDF file in the 'pdfs' folder under the 'public' disk
                $pdfpath = $pdf->store('pdfs', 'public');

                // Create a new record in the 'images' table with the profile_id, pdf_path, and type
                pdfs::create([
                    'profile_id' => $profile->id,
                    'pdf_path' => $pdfpath,
                    'type' => $type, // Store the 'type' sent by the user
                ]);
            }
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
        $qrCodeLink = 'https://ofx-qrcode.com/qr/' . $uniqueName; // Replace with your custom domain


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






///////////////////////////////////////////////
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

    $qrCode = QRCode::find($id);

    if (!$qrCode) {
        return response()->json(['message' => 'QR code not found'], 404);
    }

    // Delete the QR code
    $qrCode->delete();

    return response()->json(['message' => 'QR code deleted successfully'], 200);
}


public function downloadQRCode_image($fileName)
{

    $filePath = storage_path('app/public/qrcodes/' . $fileName);


    if (file_exists($filePath)) {

        return response()->download($filePath);
    } else {

        return response()->json(['error' => 'File not found'], 404);
    }
}





public function downloadQRCode_pdf($fileName)
{
    $filePath = storage_path('app/public/qrcodes/' . $fileName);

    if (file_exists($filePath)) {

        // HTML content for PDF generation
        $html = '
            <html>
                <head>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            text-align: center;
                            margin: 0;
                            padding: 0;
                        }
                        .container {
                            padding: 20px;
                        }
                        .logo {
                            display: block;
                            margin: 0 auto;
                            width: 200px; /* Adjust logo size as needed */
                        }
                        .qr-code {
                            display: block;
                            margin: 0 auto;
                            width: 50%;
                            max-width: 300px;
                            border: 2px solid #ddd;
                            padding: 10px;
                            border-radius: 10px;
                            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                        }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <!-- Logo at the top -->
                        <div class="logo">
                           <img src="' . public_path('storage/logos/ofx-qr-logo.png') . '" alt="OFX QRcode">
                        </div>
                        <!-- QR Code in the center -->
                        <h2>Your QR Code</h2>
                        <img src="' . public_path('storage/qrcodes/' . $fileName) . '" class="qr-code" alt="QR Code">
                    </div>
                </body>
            </html>
        ';

        // Create a PDF from the HTML content
        $pdf = PDF::loadHTML($html);

        // Return the PDF as a download
        return $pdf->download('qrcode.pdf');

    } else {
        return response()->json(['error' => 'File not found'], 404);
    }
}

}

