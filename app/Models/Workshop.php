<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Workshop extends Model
{

    use HasFactory,SoftDeletes;
   protected $fillable = [
       'title',
       'slug',
       'description',
       'image_path',
       'duration_hours',
       'price',
       'discounted_price',
       'level',
       'mode',
       'cover_image',
       'status',
       'user_id',
   ];

   public function instructor()
   {
       return $this->belongsTo(User::class, 'user_id');
   }
       public function enrollments()
    {
        return $this->morphMany(Enrollment::class, 'enrollable');
    } 
     public function plans(): MorphMany
    {
        return $this->morphMany(Plan::class, 'planable');
    }
}
