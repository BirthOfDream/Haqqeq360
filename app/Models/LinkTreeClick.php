<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LinkTreeClick extends Model
{
    protected $fillable = [
        'link_id',
        'ip_address',
        'user_agent',
        'referrer',
        'clicked_at',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
    ];

    public function link()
    {
        return $this->belongsTo(LinkTreeLink::class, 'link_id');
    }
}
    