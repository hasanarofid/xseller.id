<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'username', 'parent_id', 'position', 'left_count', 'right_count', 'left_points', 'right_points', 'package_name', 'saldo', 'total_bonus', 'security_pin', 'bonus_uncashed', 'bank_name', 'bank_account_number', 'bank_account_name'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function leftSon()
    {
        return $this->hasOne(User::class, 'parent_id')->where('position', 'left');
    }

    public function rightSon()
    {
        return $this->hasOne(User::class, 'parent_id')->where('position', 'right');
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class, 'user_id');
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class, 'user_id');
    }

    /**
     * Send custom reset password notification email.
     *
     * @param string $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        try {
            $this->notify(new \App\Notifications\CustomResetPasswordNotification($token));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengirim email reset password: ' . $e->getMessage());
        }
    }
}
