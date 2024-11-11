<?php

// app/Models/Package.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;
protected $table= 'packages';


protected $casts = [
    'features' => 'array',
    'features_ar'=> 'array'
];


    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
        'features',
        'features_ar',
        'price_dollar',
        'price_EGP',
        'max_qrcode',
    ];


    public function codes()
    {
        return $this->hasMany(code::class);
    }



    public function users()
    {
        return $this->belongsToMany(User::class, 'user_packages')
                    ->withPivot('qrcode_limit') // Add pivot data like qrcode_limit
                    ->withTimestamps(); // Automatically manage created_at and updated_at timestamps
    }


}
