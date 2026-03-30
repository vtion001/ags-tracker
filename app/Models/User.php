<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PragmaRX\Google2FA\Google2FA;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'role',
        'department',
        'position',
        'contact_number',
        'shift_schedule',
        'hire_date',
        'work_location',
        'manager_name',
        'tl_email',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'totp_secret',
        'totp_enabled',
        'totp_setup_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'totp_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'totp_enabled' => 'boolean',
            'totp_setup_at' => 'datetime',
            'hire_date' => 'date',
        ];
    }

    public function getTenureMonths(): int
    {
        if (!$this->hire_date) {
            return 0;
        }
        return (int) $this->hire_date->diffInMonths(now());
    }

    public function getWorkLocationLabel(): string
    {
        return match($this->work_location) {
            'office' => 'Office',
            'wfh' => 'Work From Home',
            'hybrid' => 'Hybrid',
            default => 'Not Set',
        };
    }

    public function activeBreak(): HasMany
    {
        return $this->hasMany(ActiveBreak::class);
    }

    public function breakHistory(): HasMany
    {
        return $this->hasMany(BreakHistory::class);
    }

    public function trustedDevices(): HasMany
    {
        return $this->hasMany(TrustedDevice::class);
    }

    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    public function isTeamLead(): bool
    {
        return $this->role === 'tl';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasActiveBreak(): bool
    {
        return $this->activeBreak()->exists();
    }

    // TOTP Methods
    public function generateTotpSecret(): string
    {
        $google2fa = new Google2FA();
        $this->totp_secret = $google2fa->generateSecretKey();
        $this->save();
        return $this->totp_secret;
    }

    public function getTotpSecret(): ?string
    {
        return $this->totp_secret;
    }

    public function verifyTotp(string $code): bool
    {
        if (!$this->totp_secret || !$this->totp_enabled) {
            return false; // TOTP must be enabled and configured
        }

        $google2fa = new Google2FA();
        return $google2fa->verifyKey($this->totp_secret, $code);
    }

    public function enableTotp(string $code): bool
    {
        $google2fa = new Google2FA();
        if ($google2fa->verifyKey($this->totp_secret, $code)) {
            $this->totp_enabled = true;
            $this->totp_setup_at = now();
            $this->save();
            return true;
        }
        return false;
    }

    public function disableTotp(): void
    {
        $this->totp_secret = null;
        $this->totp_enabled = false;
        $this->totp_setup_at = null;
        $this->save();
    }

    public function getComplianceRate(int $days = 30): int
    {
        $total = $this->breakHistory()
            ->where('started_at', '>=', now()->subDays($days))
            ->count();
        if ($total === 0) {
            return 100;
        }
        $onTime = $this->breakHistory()
            ->where('started_at', '>=', now()->subDays($days))
            ->where('over_minutes', 0)
            ->count();
        return (int) round(($onTime / $total) * 100);
    }

    public function getDailyBreaks(): int
    {
        return $this->breakHistory()
            ->whereDate('started_at', today())
            ->count();
    }

    public function getWeeklyStats(): array
    {
        $breaks = $this->breakHistory()
            ->whereBetween('started_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->get();
        return [
            'total' => $breaks->count(),
            'overbreaks' => $breaks->where('over_minutes', '>', 0)->count(),
        ];
    }

    public function getAverageDuration(string $breakType): float
    {
        $breaks = $this->breakHistory()
            ->where('break_type', $breakType)
            ->where('started_at', '>=', now()->subDays(30))
            ->whereNotNull('duration_minutes')
            ->get();
        if ($breaks->isEmpty()) {
            return 0.0;
        }
        return round($breaks->avg('duration_minutes'), 1);
    }

    public function getPerformanceScore(): int
    {
        $compliance = $this->getComplianceRate();
        $avg15 = $this->getAverageDuration('15m');
        $avg60 = $this->getAverageDuration('60m');
        $weeklyStats = $this->getWeeklyStats();

        // avg_duration_score: 100 = perfect (15m avg = 15, 60m avg = 60), decreases as overage grows
        $avg15Score = $avg15 > 0 ? max(0, 100 - (($avg15 - 15) * 10)) : 100;
        $avg60Score = $avg60 > 0 ? max(0, 100 - (($avg60 - 60) * 5)) : 100;
        $avgDurationScore = ($avg15Score + $avg60Score) / 2;

        // overbreak_ratio: 100 = no overbreaks, decreases with each overbreak
        $weekTotal = $weeklyStats['total'] > 0 ? $weeklyStats['total'] : 1;
        $overbreakRatio = max(0, 100 - ($weeklyStats['overbreaks'] / $weekTotal * 100));

        $score = ($compliance * 0.5) + ($avgDurationScore * 0.3) + ($overbreakRatio * 0.2);
        return (int) min(100, max(0, round($score)));
    }

    public function getTotpQrCodeUrl(): string
    {
        $google2fa = new Google2FA();
        return $google2fa->getQRCodeUrl(
            config('app.name'),
            $this->email,
            $this->totp_secret
        );
    }
}
