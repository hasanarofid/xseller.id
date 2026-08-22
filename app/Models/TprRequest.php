<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TprRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'package_name',
        'amount',
        'monthly_share_percent',
        'monthly_share_amount',
        'proof_of_transfer',
        'status',
        'admin_notes',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
