<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $fillable = [
        'title',
        'feature',
        'slug',
        'description1',
        'description2',

    ];
}
