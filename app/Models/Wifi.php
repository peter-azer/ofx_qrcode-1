<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wifi extends Model
{
    use HasFactory;

    protected $table = 'wifi'; // Specify the table name if it differs from the pluralized model name

    // Disable timestamps if you're not using them
    public $timestamps = false;

    // Define the fillable fields
    protected $fillable = [
        'qrcode_id',
        'name',
        'password',
        'encryption',
    ];

    // Define the relationship with the Profile model
    public function qrcode()
    {
        return $this->belongsTo(QrCodeModel::class);
    }
}
