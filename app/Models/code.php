<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class code extends Model
{
    use HasFactory;

    protected $fillable = [

        'expires_at',
        'code',
        'user_id',
        'package_id',
        'type',
    ];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            $model->code = Str::random(10);
        });
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(package::class, 'lesson_id');
}
}
