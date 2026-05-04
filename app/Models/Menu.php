<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = [
        'parent_id', 'name', 'icon', 'path', 'sort', 'status', 'type',
        'permission_code', 'permission_id',
    ];

    protected function casts(): array
    {
        return [
            'parent_id' => 'integer',
            'sort' => 'integer',
            'status' => 'integer',
            'type' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('sort');
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_menu');
    }
}
