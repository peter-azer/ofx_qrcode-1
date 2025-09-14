<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'password',
        'google_id',
        'avatar',
        'role',
        'updated_at',
        'created_at'

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'user_packages')
            ->withPivot('qrcode_limit', 'end_date', 'start_date', 'is_enable', 'created_at') // Add pivot data like qrcode_limit
            ->withTimestamps(); // Automatically manage created_at and updated_at timestamps
    }



    public function transactions()
    {
        return $this->hasMany(UserTransaction::class);
    }


    public function userLocations()
    {
        return $this->hasMany(UserLocation::class);
    }

    public function codes()
    {
        return $this->belongsTo(Code::class);
    }

    public function qrcode()
    {
        return $this->hasMany(QrCodeModel::class);
    }

    //create api for admin dashboard that get all user if has packages return his detalis  and num of his qrcodes if not  has return info of user and this user not subscribe yet 

}
