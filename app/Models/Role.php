<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = ['name', 'code', 'status'];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
        ];
    }

    public function isSuper(): bool
    {
        return $this->code === 'super_admin';
    }

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'role_menu');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user');
    }
}
