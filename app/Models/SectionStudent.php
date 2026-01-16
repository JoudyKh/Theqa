<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SectionStudent extends Model
{
    use HasFactory;

    protected $table = 'section_student' ;
    
    protected $fillable = ['student_id' , 'section_id' , 'purchase_code_id'] ;
}
