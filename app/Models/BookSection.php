<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class BookSection extends Pivot
{
    protected $fillable = ['book_id' , 'section_id'] ;
}
