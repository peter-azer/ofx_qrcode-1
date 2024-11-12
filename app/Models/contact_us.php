<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class contact_us extends Model
{
    public $timestamps = false;
    protected $fillable = [

        'email',
        'message',
        
    ];

}
