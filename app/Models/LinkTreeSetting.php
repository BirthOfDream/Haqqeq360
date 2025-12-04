<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LinkTreeSetting extends Model
{
    protected $fillable = [
        'background_color',
        'button_color',
        'text_color',
        'font_family',
        'page_title',
        'page_description',
        'slug',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function current(): ?self
    {
        return self::first();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

