<?php

namespace App\Models;

use App\Models\Concerns\SerializesDisplayDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Permission extends Model
{
    use SerializesDisplayDates;

    protected $fillable = ['code', 'name', 'remark'];

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }
}
