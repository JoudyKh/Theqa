<?php

namespace App\Models;

use App\Constants\TheqaInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Info extends Model
{
    use HasFactory;

    protected $fillable = [
        'super_key',
        'key',
        'value',
    ];

    public static function initialize()
    {


        self::$imageKeys = TheqaInfo::$imageKeys;
        self::$videoKeys = TheqaInfo::$videoKeys;
        self::$fileKeys = TheqaInfo::$fileKeys;
        self::$info = TheqaInfo::$infos;
        self::$rules = TheqaInfo::$rules;
        self::$translatableKeys = TheqaInfo::$translatableKeys;
        self::$commaSepratadKeys = TheqaInfo::$commaSepratadKeys;
    }

    public static array $info = [];
    public static array $rules = [];
    public static array $imageKeys = [];
    public static array $videoKeys = [];
    public static array $fileKeys = [];
    public static array $translatableKeys = [];
    public static array $commaSepratadKeys = [];

    public function value(): Attribute
    {
        return Attribute::make(

            get: function (mixed $value, array $attributes) {

                if (in_array($attributes['key'], static::$commaSepratadKeys)) {
                    return explode(',', $value);
                }
                if (in_array($attributes['super_key'] . '-' . $attributes['key'], static::$translatableKeys)) {
                    return json_decode($value, true);
                }
                return $value;
            },

            set: function (mixed $value, array $attributes) {

                if (in_array($attributes['key'], static::$commaSepratadKeys)) {
                    return implode(',', $value);
                }
                if (in_array($attributes['key'], static::$translatableKeys)) {
                    return json_encode($value, true) ?? $value;
                }
                return $value;
            }
        );
    }
}
