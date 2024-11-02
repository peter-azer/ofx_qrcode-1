<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class pdfs extends Model
{
    public $timestamps = false;
    protected $fillable = ['profile_id', 'pdf_path'];

    public function profile()
    {
        return $this->belongsTo(profile::class);
    }
}
