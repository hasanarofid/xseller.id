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

#[Fillable(['name', 'email', 'password', 'username', 'parent_id', 'position', 'left_count', 'right_count', 'left_points', 'right_points', 'team_points', 'ro_points', 'po_points', 'package_name', 'saldo', 'total_bonus', 'security_pin', 'bonus_uncashed', 'bank_name', 'bank_account_number', 'bank_account_name'])]
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

    public function tprRequests()
    {
        return $this->hasMany(TprRequest::class, 'user_id');
    }

    /**
     * Get base max tier generation limit according to package.
     */
    public function getBaseTier(): int
    {
        $pkg = strtolower($this->package_name ?? '');

        if (str_contains($pkg, 'partner') || str_contains($pkg, 'ultimate') || str_contains($pkg, '10.500') || str_contains($pkg, '10500')) {
            return 15;
        }
        if (str_contains($pkg, 'business') || str_contains($pkg, 'pro') || str_contains($pkg, '4.300') || str_contains($pkg, '4300')) {
            return 12;
        }
        if (str_contains($pkg, 'affiliate') || str_contains($pkg, 'medium') || str_contains($pkg, '2.100') || str_contains($pkg, '2100')) {
            return 8;
        }
        if (str_contains($pkg, 'star') || str_contains($pkg, 'basic') || str_contains($pkg, '550')) {
            return 5;
        }

        return 3;
    }

    /**
     * Calculate active max tier generation limit including stepping milestone bonuses.
     */
    public function getActiveTier(): int
    {
        $baseTier = $this->getBaseTier();
        if ($baseTier >= 15) {
            return 15;
        }

        $referralCount = static::where('parent_id', $this->id)->count();

        $milestones = [
            4 => 4,
            5 => 8,
            6 => 12,
            7 => 16,
            9 => 20,
            11 => 24,
            13 => 28,
            15 => 32,
        ];

        $activeTier = $baseTier;
        foreach ($milestones as $tier => $reqReferrals) {
            if ($tier > $baseTier && $referralCount >= $reqReferrals) {
                $activeTier = max($activeTier, $tier);
            }
        }

        return $activeTier;
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
