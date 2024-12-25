<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $fillable = [
        'title',
        'feature',
        'description1',
        'description2',
        'image1',
        'image2',
    ];
}
