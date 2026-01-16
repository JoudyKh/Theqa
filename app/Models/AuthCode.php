<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuthCode extends Model
{
    use HasFactory;
    const FORGET_PASSWORD = 'forget_password' ;
    const UPDATE_EMAIL = 'update_email' ;
    const VERIFY_EMAIL = 'verify_email' ;
    const REGISTER = 'register' ;
    const FAMILY_PHONE_NUMBER_REGISTER = 'family_phone_number_register' ;
    const UPDATE_FAMILY_PHONE_NUMBER = 'update_family_phone_number' ;

    const REQUIRED_AUTH = [
        self::UPDATE_FAMILY_PHONE_NUMBER,
        self::UPDATE_EMAIL,
        self::VERIFY_EMAIL,
    ] ;

    const ALL_TYPES = [
        self::FORGET_PASSWORD,
        self::UPDATE_EMAIL,
        self::VERIFY_EMAIL,
        self::REGISTER,
        self::FAMILY_PHONE_NUMBER_REGISTER,
        self::UPDATE_FAMILY_PHONE_NUMBER,
    ] ;

    protected $table='auth_codes';
    protected $fillable = [
        'phone_number',
        'email',
        'user_id',
        'code',
        'expired_at',
        'type',
    ];
    protected $casts = [
        'created_at' => 'date:Y-m-d h:i a',
        'updated_at' => 'date:Y-m-d h:i a',
    ];
}
