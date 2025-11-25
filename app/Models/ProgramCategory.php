<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramCategory extends Model
{
    protected $table = 'programs_categories';
    protected $fillable = [
        'name',
        'description',
    ];
}
