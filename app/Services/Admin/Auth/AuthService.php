<?php

namespace App\Services\Admin\Auth;

use App\Http\Requests\Api\General\Auth\UpdateProfileRequest;
use App\Http\Resources\UserRecourse;
use App\Models\User;
use App\Services\General\Notification\NotificationService;
use App\Services\General\User\UserService;
use Exception;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    protected ?User $user;
    protected NotificationService $notificationService;
    protected UserService $userService;

    public function __construct(NotificationService $notificationService, UserService $userService)
    {
        $this->notificationService = $notificationService;
        $this->userService = $userService;
        $this->user = auth('sanctum')->user();
    }


}
