<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class 
Option extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'question_id' ,
        'name' ,
        'is_true' ,
    ];

    public function question():BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
