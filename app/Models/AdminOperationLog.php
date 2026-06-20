<?php

namespace App\Models;

use App\Models\Concerns\SerializesDisplayDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminOperationLog extends Model
{
    use SerializesDisplayDates;

    protected $fillable = [
        'user_id', 'username', 'method', 'path', 'ip', 'action', 'payload',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
