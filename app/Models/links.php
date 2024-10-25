<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class links extends Model
{

    public $timestamps = false;
    protected $fillable = ['profile_id', 'url'];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
