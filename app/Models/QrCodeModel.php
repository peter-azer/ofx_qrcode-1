<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QrCodeModel extends Model
{
    use HasFactory;
    protected $table = 'qrcodes';
    protected $fillable = [
        'user_id',
        'profile_id',
        'qrcode',
        'link',
        'scan_count',
        'is_active',
        'package_id',
        'type',
        'created_at'
    ];
    public $timestamps = false;
    // A QR code belongs to a profile



    public function profile()
    {
        return $this->belongsTo(Profile::class,);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function UserLocation()
    {
        return $this->hasMany(UserLocation::class, 'qrcode_id');
    }

    public function whatsappMessages()
    {
        return $this->hasMany(WhatsappMessage::class, 'qr_code_id');
    }

    public function checkVisitorCount($scan_count, $package_id)
    {
        if ($package_id == 1 && $scan_count >= 20) {
            // $this->is_active = 0;
            // $this->save();
            return false; // Indicates that the QR code was deactivated
        }
        return true; // Indicates that the QR code remains active
    }
}
