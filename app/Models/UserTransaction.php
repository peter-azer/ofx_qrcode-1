<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTransaction extends Model
{
    use HasFactory;
    protected $table = 'user_transactions';
    protected $fillable = [
        'user_id',
        'package_id',
        'transaction_status',
        'amount',
        'payment_method',
        'failure_reason',
    ];

    /**
     * Relationship: UserTransaction belongs to a User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: UserTransaction belongs to a Package.
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
