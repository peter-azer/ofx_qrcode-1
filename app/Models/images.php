<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class images extends Model
{
protected $table='images';
    public $timestamps = false;
    protected $fillable = ['profile_id', 'image_path'];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
