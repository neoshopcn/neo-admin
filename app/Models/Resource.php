<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Resource extends Model
{
    protected $fillable = [
        'original_name',
        'storage_path',
        'mime_type',
        'extension',
        'size_bytes',
        'scene',
        'user_id',
        'disk',
        'status',
        'tags',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'uploaded_at' => 'datetime',
            'size_bytes' => 'integer',
            'status' => 'integer',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getPublicUrlAttribute(): ?string
    {
        try {
            return Storage::disk($this->disk)->url($this->storage_path);
        } catch (\Throwable) {
            return null;
        }
    }

    public function getTagsDisplayAttribute(): string
    {
        $t = $this->tags;
        if (! is_array($t) || $t === []) {
            return '';
        }

        return implode('、', array_map('strval', $t));
    }

    public function getSizeLabelAttribute(): string
    {
        $b = (int) $this->size_bytes;
        if ($b < 1024) {
            return $b.' B';
        }
        if ($b < 1024 * 1024) {
            return round($b / 1024, 1).' KB';
        }

        return round($b / 1024 / 1024, 2).' MB';
    }
}
