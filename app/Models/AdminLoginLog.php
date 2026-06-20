<?php

namespace App\Models;

use App\Models\Concerns\SerializesDisplayDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminLoginLog extends Model
{
    use SerializesDisplayDates;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'username',
        'ip',
        'user_agent',
        'success',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'success' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
