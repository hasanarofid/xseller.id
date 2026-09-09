<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamPointRedemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'points_used',
        'reward_amount',
        'status',
        'admin_notes',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'reward_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the reward amount for a given number of Team Points.
     * Brackets: 35 => 500.000, 90 => 1.500.000, 300 => 6.000.000,
     *           2.000 => 35.000.000, 5.000 => 80.000.000
     */
    public static function rewardForPoints(int $points): ?array
    {
        $brackets = [
            5000 => 80000000,
            2000 => 35000000,
            300  => 6000000,
            90   => 1500000,
            35   => 500000,
        ];

        foreach ($brackets as $required => $reward) {
            if ($points >= $required) {
                return ['points' => $required, 'reward' => $reward];
            }
        }

        return null;
    }
}
