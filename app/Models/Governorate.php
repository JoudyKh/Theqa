<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Governorate extends Model
{
    use HasFactory , SoftDeletes;
    protected $fillable = ['name'] ;
    public function cities():HasMany
    {
        return $this->hasMany(City::class);
    }
    public function students(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,      
            City::class,  
            'governorate_id', 
            'city_id',        
            'id',             
            'id'              
        );
    }
}
