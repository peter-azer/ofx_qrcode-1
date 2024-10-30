<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class records extends Model
{
    public $timestamps = false;
    protected $fillable = ['profile_id', 'mp3_path'];

    public function qrcode()
    {
        return $this->belongsTo(QrCodeModel::class);
    }
}

