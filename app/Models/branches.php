<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class branches extends Model
{
    public $timestamps = false;
    protected $fillable = ['profile_id', 'name', 'location', 'phones'];

 
protected $casts = [
    'phones' => 'array',
    
];
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
