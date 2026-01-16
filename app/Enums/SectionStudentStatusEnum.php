<?php

namespace App\Enums;

enum SectionStudentStatusEnum: string
{
    case PENDING = 'pending' ;
    case REJECTED =  'rejected' ;
    case ACCEPTED = 'accepted';
    public static function all():array
    {
        return array_column(self::cases() , 'value') ;
    }

}
