<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use App\Models\Concerns\RecyclesToBin;
use App\Models\Concerns\SerializesDisplayDates;
use App\Support\DateTimeFormat;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, RecyclesToBin, SerializesDisplayDates;

    protected $fillable = [
        'username',
        'name',
        'avatar',
        'email',
        'password',
        'status',
        'google2fa_secret',
        'google2fa_enabled',
        'google2fa_locked_until',
        'google2fa_last_timeslice',
    ];

    protected $appends = [
        'avatar_url',
        'role_names',
        'google2fa_is_locked',
        'google2fa_lock_label',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google2fa_secret',
        'google2fa_last_timeslice',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'integer',
            'google2fa_enabled' => 'integer',
            'google2fa_locked_until' => 'datetime',
            'google2fa_last_timeslice' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    protected function roleNames(): Attribute
    {
        return Attribute::get(function (): string {
            if (! $this->relationLoaded('roles')) {
                return '';
            }

            return $this->roles->pluck('name')->filter()->implode('、');
        });
    }

    /**
     * 任一绑定角色为超级管理员
     */
    public function hasSuperRole(): bool
    {
        $roles = $this->relationLoaded('roles')
            ? $this->roles
            : $this->roles()->get(['id', 'code', 'name']);

        return $roles->contains(fn (Role $r) => $r->isSuper());
    }

    public function isGoogle2faEnabled(): bool
    {
        return (int) $this->google2fa_enabled === 1
            && $this->google2fa_secret !== null
            && $this->google2fa_secret !== '';
    }

    public function isGoogle2faLocked(): bool
    {
        if ($this->google2fa_locked_until === null) {
            return false;
        }

        return $this->google2fa_locked_until->isFuture();
    }

    protected function google2faIsLocked(): Attribute
    {
        return Attribute::get(fn (): bool => $this->isGoogle2faLocked());
    }

    protected function google2faLockLabel(): Attribute
    {
        return Attribute::get(function (): string {
            if ($this->isGoogle2faLocked()) {
                return '至 '.DateTimeFormat::display($this->google2fa_locked_until);
            }

            return '';
        });
    }

    /**
     * 解析头像展示地址：支持 https 外链、以 / 开头的站点路径、public 磁盘相对路径
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            $raw = $this->avatar;
            if ($raw === null || $raw === '') {
                return null;
            }
            $raw = trim((string) $raw);
            if ($raw === '') {
                return null;
            }
            if (str_starts_with($raw, 'http://') || str_starts_with($raw, 'https://')) {
                return $raw;
            }
            if (str_starts_with($raw, '/')) {
                return url($raw);
            }

            return Storage::disk('public')->url($raw);
        });
    }
}
