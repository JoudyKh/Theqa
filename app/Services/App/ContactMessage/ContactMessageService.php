<?php

namespace App\Services\App\ContactMessage;

use App\Models\ContactMessage;

class ContactMessageService
{

    public function store($data): ContactMessage
    {
        return ContactMessage::create($data);
    }
}
