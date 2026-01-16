<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PurchaseCode extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'expire_date',
        'usage_limit',
    ];

    protected $appends = ['section_ids'] ;

    public function getSectionIdsAttribute(): array
    {
        return $this->sections()->pluck('sections.id')->toArray();
    }

    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'purchase_code_section' , 'purchase_code_id' , 'section_id');
    }
}