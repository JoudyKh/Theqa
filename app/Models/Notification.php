<?php

namespace App\Models;

use App\Traits\DateFormatTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Spatie\Translatable\HasTranslations;

class Notification extends Model
{
    use HasFactory , DateFormatTrait;
    // , HasTranslations;

    protected $fillable = [
        'has_read',
        'type',
        'state',
        'user_id',
        'clickable',
        'params'
    ];

    protected function asJson($value): bool|string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    // public $translatable = ['title', 'description'];
    protected $hidden = [
        'deleted_at',
    ];

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class) ;
    }

    public function asNotification()
    {
        $type = new $this->type;
        foreach ($this->attributes as $key => $value) {
            $type->setAttribute($key, $value);
        }

        return $type;
    }
}
