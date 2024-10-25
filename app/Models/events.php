<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class events extends Model
{
    public $timestamps = false;
    protected $fillable = ['profile_id', 'event_date', 'event_time', 'location'];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
