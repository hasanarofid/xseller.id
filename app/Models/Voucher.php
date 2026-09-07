<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'user_id',
        'package_name',
        'voucher_type',
        'status',
        'used_by_id',
        'used_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function usedBy()
    {
        return $this->belongsTo(User::class, 'used_by_id');
    }
}
