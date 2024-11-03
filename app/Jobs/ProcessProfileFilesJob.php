<?php
namespace App\Jobs;

use App\Models\images;
use App\Models\Pdfs;
use App\Models\records;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable; // Add this import
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessProfileFilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $profileId;
    protected $imagePaths;
    protected $pdfPaths;
    protected $mp3Paths;

    /**
     * Create a new job instance.
     */
    public function __construct($profileId, $imagePaths = [], $pdfPaths = [], $mp3Paths = [])
    {
        $this->profileId = $profileId;
        $this->imagePaths = $imagePaths;
        $this->pdfPaths = $pdfPaths;
        $this->mp3Paths = $mp3Paths;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Insert images
        if (!empty($this->imagePaths)) {
            $imageRecords = array_map(fn($path) => [
                'profile_id' => $this->profileId,
                'image_path' => $path,
            ], $this->imagePaths);

            images::insert($imageRecords);
        }

        // Insert PDFs
        if (!empty($this->pdfPaths)) {
            $pdfRecords = array_map(fn($path) => [
                'profile_id' => $this->profileId,
                'pdf_path' => $path,
            ], $this->pdfPaths);

            Pdfs::insert($pdfRecords);
        }

        // Insert MP3s
        if (!empty($this->mp3Paths)) {
            $mp3Records = array_map(fn($path) => [
                'profile_id' => $this->profileId,
                'mp3_path' => $path,
            ], $this->mp3Paths);

            records::insert($mp3Records);
        }
    }
}
