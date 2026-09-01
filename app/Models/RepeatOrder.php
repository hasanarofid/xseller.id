<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepeatOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'voucher_id',
        'voucher_code',
        'sponsor_id',
        'sponsor_bonus',
        'ro_points',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sponsor()
    {
        return $this->belongsTo(User::class, 'sponsor_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }
}
