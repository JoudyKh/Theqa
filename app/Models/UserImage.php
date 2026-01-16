<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserImage extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'user_id',
        'image'
    ];
    protected $casts = [
        'created_at' => 'date:Y-m-d h:i a',
        'updated_at' => 'date:Y-m-d h:i a',
    ];
}

