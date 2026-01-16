<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class File extends Model
{
    use HasFactory,SoftDeletes;
    
    protected $fillable = 
    [
        'model_id' ,
        'model_type' ,
        'path' ,
        'name' ,
        'url' ,
        'type' ,
        'extension' ,
        'size' ,
    ] ;

    public function model():MorphTo
    {
        return $this->morphTo() ;
    }
}
