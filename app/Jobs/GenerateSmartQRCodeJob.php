<?php


// app/Jobs/GenerateSmartQRCodeJob.php

namespace App\Jobs;

use App\Models\QrCodeModel;
use App\Models\Profile;
use App\Models\links;
use App\Models\branches;
use App\Models\records;
use App\Models\images;
use App\Models\pdfs;
use App\Models\events;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GenerateSmartQRCodeJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected $user;
    protected $validatedData;
    protected $profile;

    public function __construct($user, $validatedData, $profile)
    {
        $this->user = $user;
        $this->validatedData = $validatedData;
        $this->profile = $profile;
    }

    public function handle()
    {
        // Generate the QR code and save files as in the original function
        $uniqueName = uniqid();
        $qrCodeLink = 'https://ofx-qrcode.com/qr/' . $uniqueName;

        $qrCodeData = QrCode::format('png')
            ->backgroundColor(255, 255, 255)
            ->size(200)
            ->color(0, 0, 0)
            ->generate($qrCodeLink);

        $fileName = 'qrcodes/' . uniqid() . '.png';
        Storage::disk('public')->put($fileName, $qrCodeData);

        // Store the QR code model
        $qrCode = new QrCodeModel();
        $qrCode->profile_id = $this->profile->id;
        $qrCode->user_id = $this->user->id;
        $qrCode->qrcode = $fileName;
        $qrCode->link = $qrCodeLink;
        $qrCode->package_id = $this->validatedData['package_id'] ?? null;
        $qrCode->scan_count = 0;
        $qrCode->is_active = true;
        $qrCode->save();



        if (!empty($this->validatedData['links'])) {
            foreach ($this->validatedData['links'] as $linkData) {
                if (!empty($linkData['url']) && !empty($linkData['type'])) {
                    links::create([
                        'profile_id' => $this->profile->id,
                        'url' => $linkData['url'],
                        'type' => $linkData['type'],
                    ]);
                }
            }
        }


        if (!empty($this->validatedData['branches'])) {
            foreach ($this->validatedData['branches'] as $branchData) {
                branches::create([
                    'profile_id' => $this->profile->id,
                    'name' => $branchData['name'],
                    'location' => $branchData['location'],
                    'phones' => $branchData['phones'] ?? null, // Convert array to JSON
                ]);
            }
        }

        // Step 4: Handling MP3 files
        if (!empty($this->validatedData['mp3'])) {
            foreach ($this->validatedData['mp3'] as $mp3) {
                if ($mp3->isValid()) {
                    $mp3path = $mp3->store('records', 'public');
                    records::create([
                        'profile_id' => $this->profile->id,
                        'mp3_path' => $mp3path,
                    ]);
                }
            }
        }

        // Step 5: Handling images
        if (!empty($this->validatedData['images'])) {
            foreach ($this->validatedData['images'] as $image) {
                if ($image->isValid()) {
                    $imagePath = $image->store('images', 'public');
                    images::create([
                        'profile_id' => $this->profile->id,
                        'image_path' => $imagePath,
                    ]);
                }
            }
        }

        // Step 6: Handling PDFs
        if (!empty($this->validatedData['pdfs'])) {
            foreach ($this->validatedData['pdfs'] as $key => $pdf) {
                if ($pdf->isValid()) {
                    // Get the type for each specific PDF
                    $type = $this->validatedData['type'][$key] ?? null;

                    // Store the PDF file in the 'pdfs' folder under the 'public' disk
                    $pdfpath = $pdf->store('pdfs', 'public');

                    // Create a new record in the 'pdfs' table with the profile_id, pdf_path, and type
                    pdfs::create([
                        'profile_id' => $this->profile->id,
                        'pdf_path' => $pdfpath,
                        'type' => $type, // Store the 'type' sent by the user
                    ]);
                }
            }
        }

        // Step 7: Handling events
        if (!empty($this->validatedData['event_date'])) {
            events::create([
                'profile_id' => $this->profile->id,
                'event_date' => $this->validatedData['event_date'],
                'event_time' => $this->validatedData['event_time'] ?? null,
                'location' => $this->validatedData['location'] ?? null,
            ]);
        }
    }
}
