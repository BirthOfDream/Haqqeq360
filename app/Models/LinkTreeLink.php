<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;


class LinkTreeLink extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'url',
        'icon',
        'order',
        'is_active',
        'clicks',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'clicks' => 'integer',
        'order' => 'integer',
    ];

    public function clickRecords(): HasMany
    {
        return $this->hasMany(LinkTreeClick::class, 'link_id');
    }

    public function incrementClicks(): void
    {
        $this->increment('clicks');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    protected static function booted(): void
    {
        static::creating(function ($link) {
            if (is_null($link->order)) {
                $link->order = static::max('order') + 1 ?? 0;
            }
        });
    }
}
