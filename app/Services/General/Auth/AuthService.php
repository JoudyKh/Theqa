<?php

namespace App\Services\General\Auth;

use Auth;
use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Device;
use App\Models\AuthCode;
use App\Models\Governorate;
use App\Constants\Constants;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserRecourse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Services\General\Info\InfoService;
use App\Http\Requests\Api\General\Auth\LoginRequest;
use App\Services\App\User\UserService as AppUserService;
use App\Http\Requests\Api\General\Auth\ResetEmailRequest;
use App\Http\Requests\Api\General\Auth\ResetPhoneRequest;
use App\Services\General\Notification\NotificationService;
use App\Http\Requests\Api\General\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\General\Auth\UpdateProfileRequest;
use App\Services\General\User\UserService as GeneralUserService;
use App\Http\Requests\Api\General\Auth\ChangePasswordRequest;
use App\Http\Requests\Api\General\Auth\SendVerificationCodeRequest;
use App\Http\Requests\Api\General\Auth\CheckVerificationCodeRequest;

class AuthService
{
    protected ?User $user;
    protected NotificationService $notificationService;

    protected AppUserService $appUserService;
    protected GeneralUserService $generalUserService;
    protected InfoService $infoService;


    public function __construct(
        NotificationService $notificationService,
        AppUserService $appUserService,
        GeneralUserService $generalUserService,
        InfoService $infoService
    ) {
        $this->notificationService = $notificationService;
        $this->appUserService = $appUserService;
        $this->generalUserService = $generalUserService;
        $this->infoService = $infoService;
        $this->user = auth('sanctum')->user();
    }

    public function getProfile(Request $request): JsonResponse
    {
        $notifications = [];
        $notifications_types_stats = [];
        $notifications_count = [];


        $notifications = $this->notificationService->getAllNotifications()->getData()->data->data;
        $notifications_types_stats = $this->notificationService->getNotificationTypeStatistics(0);
        $notifications_count = $this->notificationService->getAllNotifications(0, true)->getData()->data->notifications_count;

        $extraData = [
            'notifications' => $notifications,
            //consider that the notifications comes from many types for example messages , new visit , new ad ..etc .
            'notifications_types_stats' => $notifications_types_stats,
            'notifications_count' => $notifications_count,
        ];
        //if wanted the updated the status of has_read to true then must pass read=1 param .
        if ($request->read)
            $this->notificationService->readAllNotifications();

        return success(
            UserRecourse::make($this->user->load(['images', 'city'])),
            200,
            $extraData
        );
    }

    /**
     * @throws Exception
     */
    public function login(LoginRequest $request, $isAdmin = false)
    {
        $fun = $isAdmin ? 'whereHas' : 'whereDoesntHave';
        $user = User::where('username', $request->username)
                    ->orWhere('phone_number', $request->username)
                    ->orWhere('email', $request->username)
            ->$fun('roles', function ($q) {
                $q->where('name', Constants::ADMIN_ROLE);
            })->first();

        if (!$user)
            throw new Exception(__('messages.username_is_not_correct'), 422);

        if ($user->is_banned) {
            return response()->json([
                'message' => __('messages.you_are_banned'),
                'info' => $this->infoService->getAll(),
            ], 403);
        }

        if (!Hash::check($request->password, $user->password))
            throw new Exception(__('messages.wrong_password'), 422);

        if (request()->hasHeader('fingerprint')) {
            Device::updateOrCreate([
                'user_id' => $user->id,
                'fingerprint' => request()->header('fingerprint'),
            ], [
                'agent' => $request->userAgent(),
            ]);
        }

        $token = $user->createToken('auth')->plainTextToken;
        if ($request->fcm_token) {
            $this->appUserService->handleFcmToken($user, $request->fcm_token);
        }
        $user['token'] = $token;

        return success(['user' => new UserRecourse($user)]);
    }

    function logout(): true
    {
        if (request()->hasHeader('fingerprint')) {
            $this->user->devices()->where(['fingerprint' => request()->header('fingerprint')])->delete();
        }

        $this->user->tokens()->where('id', $this->user->currentAccessToken()->id)->delete();
        return true;
    }

    /**
     * @throws Exception
     */
    public function changePassword(ChangePasswordRequest $request): true
    {

        if (Hash::check($request->old_password, $this->user->password)) {
            $this->user->update(
                ['password' => Hash::make($request->password)]
            );
            return true;
        }
        throw new Exception(__('messages.wrong_old_password'), 422);
    }

    /**
     * @throws Exception
     */
    public function resetPassword(ResetPasswordRequest $request): true
    {
        $passwordResetCode = AuthCode::where('email', $request->email)
            ->where('code', $request->verification_code)
            ->first();

        if (!$passwordResetCode) {
            throw new Exception(__('messages.invalid_verification_code'), 422);
        }

        $passwordResetCode->delete();
        $user = User::where('email', $request->email)->first();
        $user->update(
            ['password' => Hash::make($request->password)]
        );
        return true;
    }

    /**
     * @throws Exception
     */
    public function resetPhone(ResetPhoneRequest $request): true
    {
        $code = AuthCode::where('phone_number', $request->family_phone_number)
            ->where('type', AuthCode::UPDATE_FAMILY_PHONE_NUMBER)
            ->where('user_id', auth('sanctum')->id())
            ->where('code', $request->verification_code)
            ->first();

        if ($code and Carbon::parse($code->expired_at)->isPast()) {
            throw new Exception(__('messages.expired_verification_code'), 422);
        }

        if (!$code) {
            throw new Exception(__('messages.invalid_verification_code'), 422);
        }
        $code->delete();

        $this->user->update(['family_phone_number' => $request->family_phone_number]);

        return true;
    }

    /**
     * @throws Exception
     */
    public function resetEmail(ResetEmailRequest $request): true
    {
        if ($request->email === null) {
            $this->user->update(['email' => null]);
            return true;
        }

        $code = AuthCode::where('email', $request->email)
            ->where('type', AuthCode::UPDATE_EMAIL)
            ->where('user_id', auth('sanctum')->id())
            ->where('code', $request->verification_code)
            ->first();

        if ($code and Carbon::parse($code->expired_at)->isPast()) {
            throw new Exception(__('messages.expired_verification_code'), 422);
        }

        if (!$code) {
            throw new Exception(__('messages.invalid_verification_code'), 422);
        }
        $code->delete();

        $this->user->update(['email' => $request->email]);

        return true;
    }

    public function checkVerificationCode(CheckVerificationCodeRequest $request): array
    {
        $whereQuery = [
            'phone_number' => $request->phone_number ?? $request->family_phone_number,
            'email' => $request->email,
            'user_id' => $this->user?->id,
            'code' => $request->verification_code,
        ];


        $response = [];
        $code = AuthCode::where($whereQuery)->first();

        if ($code and Carbon::parse($code->expired_at)->isPast()) {
            throw new Exception(__('messages.expired_verification_code'), 422);
        }

        // this part for activation after signup if needed .
        if ($this->user and $request->get('type') == AuthCode::VERIFY_EMAIL) {
            if (!$code) {
                throw new Exception(__('messages.invalid_verification_code'), 422);
            }

            if ($request->get('type') !== AuthCode::VERIFY_EMAIL) {
                $this->user->update(['is_active' => 1, 'email_verified_at' => Carbon::now()]);
                $code->delete();
                $response['message'] = __('messages.your_account_has_been_activated');
            }

            return $response;
        }

        $response = [
            'code' => $request->verification_code,
            'is_valid' => $code ? true : false,
        ];

        return $response;
    }


    /**
     * @throws Exception
     */
    public function sendVerificationCode(SendVerificationCodeRequest &$request): true
    {
        return DB::transaction(function () use ($request) {
            $authToVerify = [];
            $emailToSend = null;
            $phoneNumberToSend = null;
            // this part for activation after signup if needed .
            if ($this->user) {

                if ($request->get('type') == AuthCode::VERIFY_EMAIL and $this->user->is_active) {
                    throw new Exception(__('messages.you_have_already_activate_your_account'), 422);
                }

                $authToVerify['user_id'] = $this->user->id;
                AuthCode::where('user_id', $this->user->id)->delete();
            }

            if ($request->email) {
                $authToVerify['email'] = $request->email;
                AuthCode::where('email', $request->email)->delete();
            }

            if ($request->family_phone_number) {
                $authToVerify['phone_number'] = $request->family_phone_number;
                AuthCode::where('phone_number', $request->family_phone_number)
                    ->whereIn('type', [
                        AuthCode::FAMILY_PHONE_NUMBER_REGISTER,
                        AuthCode::UPDATE_FAMILY_PHONE_NUMBER,
                    ])
                    ->delete();
            }

            if ($request->get('type') == AuthCode::UPDATE_FAMILY_PHONE_NUMBER) {
                $phoneNumberToSend = $request->family_phone_number;
            } elseif ($request->get('type') == AuthCode::FAMILY_PHONE_NUMBER_REGISTER) {
                $phoneNumberToSend = $request->family_phone_number;
            } elseif ($request->get('type') == AuthCode::VERIFY_EMAIL) {
                $emailToSend = $this->user->email;
            } elseif ($request->get('type') == AuthCode::REGISTER) {
                $emailToSend = $request->email;
            } elseif ($request->get('type') == AuthCode::FORGET_PASSWORD) {
                $emailToSend = ($this->user ? $this->user->email : $request->email);
            } elseif ($request->get('type') == AuthCode::UPDATE_EMAIL) {
                $emailToSend = $request->email;
                $authToVerify['email'] = $request->email;
            } else {
                throw new Exception('invalid type');
            }

            $code = rand(1000, 9999);

            $details = [
                'title' => __('messages.your_verification_code_is'),
                'body' => $code,
            ];

            $expired_at = Carbon::now()->addMinutes(15)->format('Y-m-d H:i');
            if ($emailToSend) {
                Mail::to($emailToSend)->send(new \App\Mail\VerificationCode($details));
            } elseif ($phoneNumberToSend) {
                $message = __('messages.whats_app_auth_code', [
                    'code' => $code,
                    'expired_at' => $expired_at
                ]);

                pushWhatsAppMessage($message, $phoneNumberToSend, $this->user?->id);
            }

            $data = array_merge([
                'code' => $code,
                'expired_at' => $expired_at,
            ], $authToVerify, [
                'type' => $request->get('type')
            ]);

            AuthCode::create($data);
            return true;

        });
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {

        $data = $request->validated();
        if ($request->has('password')) {
            if (!Hash::check($request->old_password, $this->user->password)) {
                throw new Exception(__('messages.wrong_old_password'), 422);
            }
            $data['password'] = Hash::make($request->password);
        }

        if ($this->user->images()->count() - count($request->trash_images_ids ?? []) + count($request->images ?? []) > 4) {
            throw new Exception(__('messages.maximum_images_count'), 422);
        }
        /**this for multi images  */
        // if (is_array($request->trash_images_ids))
        //     $this->user->images()->whereIn('id', $request->trash_images_ids)->delete();
        // if (is_array($request->images))
        //     foreach ($request->images as $image) {
        //         $this->user->images()->create([
        //             'image' => $image->storePublicly('users/profile', 'public'),
        //         ]);
        //     }


        $this->generalUserService->handleUserImage($this->user, $request);
        $this->user->update($data);

        //if the user has notification
        $notifications = $this->notificationService->getAllNotifications();

        return success(
            UserRecourse::make(User::with('images')
                ->where('id', $this->user->id)->first()),
            200,
            [
                'notifications' => $notifications ?? null,
                //consider that the notifications comes from many types for example messages , new visit , new ad ..etc .
                'notifications_types_stats' => $this->notificationService?->getNotificationTypeStatistics(0) ?? [],
                'notifications_count' => $this->notificationService?->getAllNotifications(0, true) ?? [],
            ]
        );
    }

}
