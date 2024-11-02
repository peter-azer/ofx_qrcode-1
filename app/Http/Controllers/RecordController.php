<?php

namespace App\Http\Controllers;


use App\Models\images;
use App\Models\Pdf;
use App\Models\pdfs;
use App\Models\records;
use Illuminate\Http\Request;

class RecordController extends Controller
{
    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'images' => 'nullable|array|min:1',
            'images.*' => 'file|mimes:jpeg,png,jpg',
            'mp3' => 'nullable|array',
            'mp3.*' => 'file',
            'pdfs' => 'required|array|min:1',
            'pdfs.*' => 'file|mimes:pdf',
        ]);


        // Handle Images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath = $image->store('images', 'public'); // Store in 'storage/app/public/images'
                images::create(['image_path' => $imagePath]);
            }
        }

        // Handle PDFs
        if ($request->hasFile('pdfs')) {
            foreach ($request->file('pdfs') as $pdf) {
                $pdfPath = $pdf->store('pdfs', 'public'); // Store in 'storage/app/public/pdfs'
                pdfs::create(['pdf_path' => $pdfPath]);
            }
        }

        // Handle MP3 files
        if ($request->hasFile('mp3')) {
            foreach ($request->file('mp3') as $audio) {
                $mp3Path = $audio->store('mp3', 'public'); // Store in 'storage/app/public/mp3'
                records::create(['mp3_path' => $mp3Path,
                'profile_id' => '1'
            ]);
            }
        }

        return response()->json(['message' => 'Files uploaded successfully!'], 201);
    }
}
