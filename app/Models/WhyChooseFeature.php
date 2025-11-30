<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhyChooseFeature extends Model
{
    protected $fillable = [
        'title',
        'description',
        'icon',
        'is_active',
        'display_order',
    ];
}
