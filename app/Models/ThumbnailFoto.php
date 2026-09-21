<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ThumbnailFoto extends Model
{
    protected $table = 'thumbnail_foto';

    protected $fillable = [
        'thumbnail_id',
        'path',
        'urutan',
        'fokus_x',
        'fokus_y',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
            'fokus_x' => 'integer',
            'fokus_y' => 'integer',
        ];
    }

    public function thumbnail(): BelongsTo
    {
        return $this->belongsTo(Thumbnail::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
