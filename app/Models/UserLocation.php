<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLocation extends Model
{
    protected $table = 'user_location';

    protected $fillable = [ 'qrcode_id', 'location'];

 
    public function qrCode()
    {
        return $this->belongsTo(QrCodeModel::class);
    }
}
