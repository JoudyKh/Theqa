<?php

namespace App\Services\App\Auth;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\AuthCode;
use App\Constants\Constants;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UserRecourse;
use Illuminate\Support\Facades\Hash;
use App\Services\General\User\UserService;
use Illuminate\Foundation\Http\FormRequest;
use App\Services\General\Notification\NotificationService;
use App\Http\Requests\Api\General\Auth\UpdateProfileRequest;

class AuthService
{
    protected ?User $user;


    public function __construct(protected NotificationService $notificationService, protected UserService $userService)
    {

        $this->user = auth('sanctum')->user();
    }


    public function register(FormRequest &$request): array
    {
        if ($request->has('verification_code')) {
            $code = AuthCode::where('email', $request->email)
                ->where('type', AuthCode::REGISTER)
                ->where('code', $request->verification_code)
                ->first();

            if ($code and Carbon::parse($code->expired_at)->isPast()) {
                throw new Exception(__('messages.expired_verification_code'), 422);
            }

            if (!$code) {
                throw new Exception(__('messages.invalid_verification_code'), 422);
            }

            $code->delete();
        }

        if ($request->has('family_phone_number_verification_code')) {
            $code = AuthCode::where('phone_number', $request->family_phone_number)
                ->where('type', AuthCode::FAMILY_PHONE_NUMBER_REGISTER)
                ->where('code', $request->family_phone_number_verification_code)
                ->first();

            if ($code and Carbon::parse($code->expired_at)->isPast()) {
                throw new Exception(__('messages.expired_verification_code'), 422);
            }

            if (!$code) {
                throw new Exception(__('messages.invalid_verification_code'), 422);
            }

            $code->delete();
        }

        $user = $this->userService->createUser($request, Constants::STUDENT_ROLE, true);
        $user->assignRole(Constants::STUDENT_ROLE);



        return ['user' => new UserRecourse($user->loadMissing(['roles', 'images']))];
    }
}
