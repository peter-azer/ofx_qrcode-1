<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PhpParser\Node\Expr\Cast;

class Profile extends Model
{


    use HasFactory;
      protected $table = 'profiles';
public $timestamps = false;
    protected $fillable = [
        'user_id', 'cover', 'logo', 'background_color' , 'font','title','description',
    ];


    protected $Casts = [
        'phones'=> 'array',
    ];

    // A profile belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // A profile has many QR codes
    public function qrCodes()
    {
        return $this->belongsTo(QrCodeModel::class);
    }
    public function links()
    {
        return $this->hasMany(links::class);
    }

    public function images()
    {
        return $this->hasMany(images::class);
    }

    public function pdfs()
    {
        return $this->hasMany(pdfs::class);
    }

    public function events()
    {
        return $this->hasMany(events::class);
    }
}
