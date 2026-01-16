<?php

namespace App\Constants;

class Notifications
{
    const NEW_STUDENT_SECTION = [
        'TITLE' => '! تهانينا',
        'DESCRIPTION' => 'تم اضافتك الى كورس جديد',
        'TYPE' => 'NEW_STUDENT_SECTION',
        'STATE' => 0,
    ];

    const SUB_REQUEST_ACCEPTED = [
        'TITLE' => '! تهانينا',
        'DESCRIPTION' => 'تمت الموافقة على طلب الاشتراك',
        'TYPE' => 'SUB_REQUEST_ACCEPTED',
        'STATE' => 1,
    ];

    /**
     * Get all notifications dynamically.
     *
     * @return array
     */
    public static function getAuthNotifications()
    {
        $reflection = new \ReflectionClass(__CLASS__);
        return $reflection->getConstants();
    }
}
