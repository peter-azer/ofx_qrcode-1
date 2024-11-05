<?php

// app/Models/Subscription.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory;
protected $table='subscriptions';
    protected $fillable = [
        'user_id',
        'package_id',
        'start_date',
        'end_date',
        'duration',
        'is_enable',
    ];

    protected $dates = ['start_date', 'end_date'];

    // Relation with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relation with Package
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    // Calculate the end date based on the duration
    public static function calculateEndDate($startDate, $duration)
    {
        switch ($duration) {
            case 'month':
                return $startDate->addMonth();
            case 'three_months':
                return $startDate->addMonths(3);
            case 'year':
                return $startDate->addYear();
            default:
                return $startDate;
        }
    }
}
