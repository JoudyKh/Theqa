<?php

namespace App\Models;

use App\Traits\DateFormatTrait;
use App\Traits\CreatedAtDescScopeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Slider extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'type',
        'image',
        'title',
        'description',
        'phone',
    ];
    const HERO_TYPE = 'hero';
    const OUR_FREATURES_TYPE = 'our_features';
    const LOCATIONS = 'locations';

    public static $types = [
        self::HERO_TYPE => [
            'attributes' =>
                [
                    'image',
                    'title',
                    'description',
                ],
            'rules' =>
                [
                    'create' => [
                        'image' => 'nullable|image|mimes:png,jpg,jpeg',
                        'title' => 'nullable|string|max:255',
                        'description' => 'nullable|string',
                    ],
                    'update' => [
                        'image' => 'nullable|image|mimes:png,jpg,jpeg',
                        'title' => 'nullable|string|max:255',
                        'description' => 'nullable|string',
                    ],
                ]
        ],
        self::OUR_FREATURES_TYPE => [
            'attributes' =>
                [
                    'image',
                    'title',
                    'description',
                ],
            'rules' =>
                [
                    'create' => [
                        'image' => 'nullable|image|mimes:png,jpg,jpeg',
                        'title' => 'required|string|max:255',
                        'description' => 'required|string',
                    ],
                    'update' => [
                        'image' => 'nullable|image|mimes:png,jpg,jpeg',
                        'title' => 'string|max:255',
                        'description' => 'string',
                    ],
                ]
        ],
        self::LOCATIONS => [
            'attributes' =>
                [
                    'image',
                    'title',
                    'phone',
                ],
            'rules' =>
                [
                    'create' => [
                        'image' => 'nullable|image|mimes:png,jpg,jpeg',
                        'title' => 'required|string|max:255',
                        'phone' => 'required|string|max:255',
                    ],
                    'update' => [
                        'image' => 'nullable|image|mimes:png,jpg,jpeg',
                        'title' => 'string|max:255',
                        'phone' => 'string|max:255',
                    ],
                ]
        ],
    ];

    public static $homeTypes = [
        self::HERO_TYPE,
        self::LOCATIONS,
        self::OUR_FREATURES_TYPE,
    ];
}