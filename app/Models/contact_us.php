<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class contact_us extends Model
{   public $timestamps = false;
    protected $table = 'contact_us';
 
    protected $fillable = [

        'email',
        'message',
        
    ];

}
