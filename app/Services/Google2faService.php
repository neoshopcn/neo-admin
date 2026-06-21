<?php

namespace App\Services;

use App\Models\User;
use App\Support\GoogleAuthenticator;

class Google2faService
{
    public function __construct(
        protected GoogleAuthenticator $authenticator
    ) {}

    /**
     * @return array{secret: string, qr_url: string}
     */
    public function enable(User $user): array
    {
        if ($user->google2fa_secret === null || $user->google2fa_secret === '') {
            $user->google2fa_secret = $this->authenticator->createSecret();
        }

        $user->google2fa_enabled = 1;
        $user->google2fa_locked_until = null;
        $user->google2fa_last_timeslice = null;
        $user->save();

        $issuer = (string) config('app.name', 'NeoAdmin');
        $label = $user->username.'@'.($user->name??$user->username);
        $secret = (string) $user->google2fa_secret;

        return [
            'secret' => $secret,
            'qr_url' => $this->authenticator->getQRCodeGoogleUrl($label, $secret, $issuer),
        ];
    }

    public function disable(User $user): void
    {
        $user->google2fa_enabled = 0;
        $user->google2fa_secret = null;
        $user->google2fa_locked_until = null;
        $user->google2fa_last_timeslice = null;
        $user->save();
    }

    public function unlock(User $user): void
    {
        $user->google2fa_locked_until = null;
        $user->save();
    }

    public function verify(User $user, string $code): bool
    {
        $secret = $user->google2fa_secret;
        if ($secret === null || $secret === '') {
            return false;
        }

        $minSlice = $user->google2fa_last_timeslice !== null
            ? (int) $user->google2fa_last_timeslice
            : null;

        $slice = $this->authenticator->findValidTimeSlice(
            $secret,
            $code,
            $this->discrepancy(),
            null,
            $minSlice
        );

        if ($slice === null) {
            return false;
        }

        $user->google2fa_last_timeslice = $slice;
        $user->save();

        return true;
    }

    public function isLocked(User $user): bool
    {
        if ($user->google2fa_locked_until === null) {
            return false;
        }

        return $user->google2fa_locked_until->isFuture();
    }

    public function lock(User $user): void
    {
        $minutes = max(1, (int) config('admin.google2fa.lock_minutes', 15));
        $user->google2fa_locked_until = now()->addMinutes($minutes);
        $user->save();
    }

    public function maxAttempts(): int
    {
        return max(1, (int) config('admin.google2fa.max_attempts', 5));
    }

    public function discrepancy(): int
    {
        return max(0, (int) config('admin.google2fa.discrepancy', 0));
    }
}
