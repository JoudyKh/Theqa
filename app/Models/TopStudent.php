<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

//the logic moved to User model
//the top student became real User
class TopStudent extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = ['name' , 'degree' , 'description' , 'image'] ;
}