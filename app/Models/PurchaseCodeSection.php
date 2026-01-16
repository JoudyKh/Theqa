<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseCodeSection extends Model
{
    use HasFactory;

    protected $table = 'purchase_code_section' ;
    protected $fillable = ['section_id' , 'purchase_code_id'] ;
}
