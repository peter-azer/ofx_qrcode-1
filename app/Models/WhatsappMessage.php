<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappMessage extends Model
{

    public $timestamps = false;
    protected $fillable = ['qr_code_id', 'phone_number', 'message'];

    public function qrCode()
    {
        return $this->belongsTo(QrCodeModel::class);
    }
}
