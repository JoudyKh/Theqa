<?php

namespace App\Http\Controllers\Api\General\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\General\Auth\AuthService;
use App\Http\Requests\Api\General\Auth\LoginRequest;
use App\Http\Requests\Api\General\Auth\ResetEmailRequest;
use App\Http\Requests\Api\General\Auth\ResetPhoneRequest;
use App\Http\Requests\Api\General\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\General\Auth\UpdateProfileRequest;
use App\Http\Requests\Api\General\Auth\ChangePasswordRequest;
use App\Http\Requests\Api\General\Auth\SendVerificationCodeRequest;
use App\Http\Requests\Api\General\Auth\CheckVerificationCodeRequest;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @OA\Post(
     *      path="/login",
     *      operationId="app/login",
     *      summary="Login",
     *     security={{"lmsAuth": {}}},
     *     tags={"App", "App - Auth"},
     *     @OA\Parameter(
     *         name="fingerprint",
     *         in="header",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="App Login",
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="username", type="string", example="student"),
     *                  @OA\Property(property="fcm_token", type="string", example="#####"),
     *                  @OA\Property(property="password", type="string", example="12345678"),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(response=200, description="Successful operation"),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="The given data was invalid"),
     *              @OA\Property(property="errors", type="object", example={"username": {"The username field is required."}})
     *          )
     *      )
     *  )
     *
     * @OA\Post(
     *     path="/admin/login",
     *     operationId="admin/login",
     *     summary="Login",
     *    tags={"Admin", "Admin - Auth"},
     *    security={{"lmsAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Admin Login",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="username", type="string", example="admin"),
     *                 @OA\Property(property="fcm_token", type="string", example="#####"),
     *                 @OA\Property(property="password", type="string", example="12345678"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid"),
     *             @OA\Property(property="errors", type="object", example={"username": {"The username field is required."}})
     *         )
     *     )
     * )
     */

    public function login(LoginRequest $request)
    {
        try {
            return $this->authService->login($request, str_contains($request->url(), 'admin'));
        } catch (\Exception $e) {
            return error($e->getMessage(), null, 400);
        }
    }


    /**
     * @OA\Get(
     *     path="/profile",
     *     operationId="app/profile",
     *     summary="get profile data ",
     *     tags={"App", "App - Auth"},
     *     security={{"lmsAuth": {}}},
     *      @OA\Parameter(
     *     name="read",
     *     in="query",
     *     description="pass it as 1 if wanted the notification to be read ",
     *     required=false,
     *     @OA\Schema(
     *         type="integer",
     *          enum={1,0}
     *     )
     *      ),
     *    security={{ "bearerAuth": {} ,"lmsAuth": {}, "Accept": "json/application" }},
     *    @OA\Response(response=200, description="Successful operation"),
     * )
     *
     * @OA\Get(
     *      path="/admin/profile",
     *      operationId="admin/profile",
     *      summary="get profile data ",
     *      security={{"lmsAuth": {}}},
     *      tags={"Admin", "Admin - Auth"},
     *       @OA\Parameter(
     *      name="read",
     *      in="query",
     *      description="pass it as 1 if wanted the notification to be read ",
     *      required=false,
     *      @OA\Schema(
     *          type="integer",
     *           enum={1,0}
     *      )
     *       ),
     *     security={{ "bearerAuth": {} ,"lmsAuth": {}, "Accept": "json/application" }},
     *     @OA\Response(response=200, description="Successful operation"),
     *  )
     */

     
    public function profile(Request $request)
    {
        try {
            return $this->authService->getProfile($request);
        } catch (\Exception $e) {
            return error($e->getMessage(), [$e->getMessage()], $e->getCode());
        }
    }

    /**
     * @OA\Get(
     *     path="/check/auth",
     *     operationId="app/check/auth",
     *     summary="Check Auth",
     *     tags={"App", "App - Auth"},
     *     security={{"lmsAuth": {}}},
     *    security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid"),
     *             @OA\Property(property="errors", type="object", example={"password": {"The password field is required."}})
     *         )
     *     )
     * )
     *
     * @OA\Get(
     *      path="/admin/check/auth",
     *      operationId="admin/check/auth",
     *      security={{"lmsAuth": {}}},
     *      summary="Check Auth",
     *     tags={"Admin", "Admin - Auth"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *      @OA\Response(response=200, description="Successful operation"),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="The given data was invalid"),
     *              @OA\Property(property="errors", type="object", example={"password": {"The password field is required."}})
     *          )
     *      )
     *  )
     */
    public function authCheck(): JsonResponse
    {
        return success();
    }

    /**
     * @OA\Post(
     *     path="/logout",
     *     operationId="app/logout",
     *     security={{"lmsAuth": {}}},
     *     summary="App Logout",
     *    tags={"App", "App - Auth"},
     *    security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *    @OA\Parameter(
     *         name="fingerprint",
     *         in="header",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successfully logged out",
     *     ),
     * )
     * @OA\Post(
     *      path="/admin/logout",
     *      security={{"lmsAuth": {}}},
     *      operationId="admin/logout",
     *      summary="Admin Logout",
     *     tags={"Admin", "Admin - Auth"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="successfully logged out",
     *      ),
     *  )
     */
    function logout()
    {
        try {
            $this->authService->logout();
            return success(['message' => __('messages.successfully_logged_out')]);
        } catch (\Exception $e) {
            return error($e->getMessage(), null, $e->getCode());
        }
    }


    /**
     * @OA\Post(
     *     path="/change-password",
     *     operationId="change-password",
     *     summary="Change password",
     *     security={{"lmsAuth": {}}},
     *    tags={"App", "App - Auth"},
     *    security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Change password",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="_method", type="string", example="PUT"),
     *                 @OA\Property(property="old_password", type="string", example="12345678"),
     *                 @OA\Property(property="password", type="string", example="password"),
     *                 @OA\Property(property="password_confirmation", type="string", example="password"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successfully Changed password",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid"),
     *             @OA\Property(property="errors", type="object", example={"password": {"The password field is required."}})
     *         )
     *     )
     * )
     * @OA\Post(
     *      path="/admin/change-password",
     *      operationId="admin/change-password",
     *      summary="Change password",
     *      security={{"lmsAuth": {}}},
     *     tags={"Admin", "Admin - Auth"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Change password",
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="_method", type="string", example="PUT"),
     *                  @OA\Property(property="old_password", type="string", example="12345678"),
     *                  @OA\Property(property="password", type="string", example="password"),
     *                  @OA\Property(property="password_confirmation", type="string", example="password"),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully Changed password",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="The given data was invalid"),
     *              @OA\Property(property="errors", type="object", example={"password": {"The password field is required."}})
     *          )
     *      )
     *  )
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $this->authService->changePassword($request);
            return success(__('messages.password_updated_successfully'));
        } catch (\Exception $e) {
            return error($e->getMessage(), null, $e->getCode());
        }
    }

    /**
     * @OA\Post(
     *     path="/reset-password",
     *     operationId="reset-password",
     *     summary="Reset password",
     *    tags={"App", "App - Auth"},
     *    security={{"lmsAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Reset password",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="password", type="string", example="12345678"),
     *                 @OA\Property(property="password_confirmation", type="string", example="12345678"),
     *                 @OA\Property(property="verification_code", type="string", example="1234"),
     *                 @OA\Property(property="email", type="string", example="yosofbayan75@gmail.com"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successfully Changed password",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid"),
     *             @OA\Property(property="errors", type="object", example={"password": {"The password field is required."}})
     *         )
     *     )
     * )
     *
     * @OA\Post(
     *      path="/admin/reset-password",
     *      operationId="admin/reset-password",
     *      summary="Reset password",
     *      security={{"lmsAuth": {}}},
     *     tags={"Admin", "Admin - Auth"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Reset password",
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="password", type="string", example="12345678"),
     *                  @OA\Property(property="password_confirmation", type="string", example="12345678"),
     *                  @OA\Property(property="verification_code", type="string", example="1234"),
     *                  @OA\Property(property="email", type="string", example="yosofbayan75@gmail.com"),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully Changed password",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="The given data was invalid"),
     *              @OA\Property(property="errors", type="object", example={"password": {"The password field is required."}})
     *          )
     *      )
     *  )
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $this->authService->resetPassword($request);
            return success(__('messages.password_updated_successfully'));
        } catch (\Exception $e) {
            return error($e->getMessage(), null, $e->getCode());
        }
    }


    /**
     * @OA\Post(
     *     path="/update-email",
     *     operationId="update-email",
     *     summary="update email",
     *     tags={"App", "App - Auth"},
     *     security={{"lmsAuth": {}}},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="update email",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="email", type="string", example="eamil@gmail.com"),
     *                 @OA\Property(property="verification_code", type="string", example="1234"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successfully Changed password",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid"),
     *             @OA\Property(property="errors", type="object", example={"password": {"The password field is required."}})
     *         )
     *     )
     * )
     *
     * @OA\Post(
     *      path="/admin/update-email",
     *      operationId="admin/update-email",
     *      summary="update email",
     *      security={{"lmsAuth": {}}},
     *      tags={"Admin", "Admin - Auth"},
     *      security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Update email",
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="verification_code", type="string", example="1234"),
     *                  @OA\Property(property="email", type="string", example="yosofbayan75@gmail.com"),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully Changed password",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="The given data was invalid"),
     *              @OA\Property(property="errors", type="object", example={"password": {"The password field is required."}})
     *          )
     *      )
     *  )
     */
    public function resetEmail(ResetEmailRequest $request): JsonResponse
    {
        try {
            $this->authService->resetEmail($request);
            return success(__('messages.email_updated_successfully'));
        } catch (\Exception $e) {
            return error($e->getMessage(), null, $e->getCode());
        }
    }

    /**
     * @OA\Post(
     *     path="/update-phone",
     *     operationId="update-phone",
     *     summary="update phone",
     *     tags={"App", "App - Auth"},
     *     security={{"lmsAuth": {}}},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="update phone",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="family_phone_number", type="string", example="996725629"),
     *                 @OA\Property(property="verification_code", type="string", example="1234"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successfully Changed password",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid"),
     *             @OA\Property(property="errors", type="object", example={"password": {"The password field is required."}})
     *         )
     *     )
     * )
     *
     * @OA\Post(
     *      path="/admin/update-phone",
     *      operationId="admin/update-phone",
     *      summary="update phone",
     *      security={{"lmsAuth": {}}},
     *      tags={"Admin", "Admin - Auth"},
     *      security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Update phone",
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="verification_code", type="string", example="1234"),
     *                 @OA\Property(property="family_phone_number", type="string", example="996725629"),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully Changed password",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="The given data was invalid"),
     *              @OA\Property(property="errors", type="object", example={"password": {"The password field is required."}})
     *          )
     *      )
     *  )
     */
    public function resetPhone(ResetPhoneRequest $request): JsonResponse
    {
        try {
            $this->authService->resetPhone($request);
            return success(__('messages.phone_updated_successfully'));
        } catch (\Exception $e) {
            return error($e->getMessage(), null, $e->getCode());
        }
    }
    
    /**
     * @OA\Post(
     *     path="/check/verification-code",
     *     operationId="check-verification-code",
     *     summary="check verification-code",
     *     tags={"App", "App - Auth"},
     *     security={{"lmsAuth": {}}},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"forget_password","update_email","register","family_phone_number_register","update_family_phone_number"} ,
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Check verification-code",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="verification_code", type="string", example="1234"),
     *                 @OA\Property(property="email", type="string", example="yosofbayan75@gmail.com"),
     *                 @OA\Property(property="family_phone_number", type="string", example="0996725629"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="oK",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid"),
     *             @OA\Property(property="errors", type="object", example={"password": {"The password field is required."}})
     *         )
     *     )
     * )
     * @OA\Post(
     *      path="/admin/check/verification-code",
     *      operationId="admin/check-verification-code",
     *      summary="check verification-code",
     *      tags={"Admin", "Admin - Auth"},
     *      security={{"lmsAuth": {}}},
     *      security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *      @OA\Parameter(
     *         name="type",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"forget_password","update_email","register","family_phone_number_register","update_family_phone_number"} ,
     *         )
     *     ),   
     *      @OA\RequestBody(
     *          required=true,
     *          description="Check verification-code",
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="verification_code", type="string", example="1234"),
     *                  @OA\Property(property="email", type="string", example="yosofbayan75@gmail.com"),
     *                  @OA\Property(property="family_phone_number", type="string", example="0996725629"),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="oK",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="The given data was invalid"),
     *              @OA\Property(property="errors", type="object", example={"password": {"The password field is required."}})
     *          )
     *      )
     *  )
     */
    public function checkVerificationCode(CheckVerificationCodeRequest $request): JsonResponse
    {
        try {
            $response = $this->authService->checkVerificationCode($request);
            return success($response);
        } catch (\Exception $e) {
            return error($e->getMessage(), null, $e->getCode());
        }
    }

    /**
     * @OA\Post(
     *     path="/send/verification-code",
     *     operationId="send-verification-code",
     *     summary="send verification code ",
     *     tags={"App", "App - Auth"},
     *     security={{"lmsAuth": {}}},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"forget_password","update_email","register","family_phone_number_register","update_family_phone_number"} ,
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="send verification-code",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="email", type="string", example="yosofbayan75@gmail.com"),
     *                 @OA\Property(property="family_phone_number", type="string", example="0996725629"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successfully sent",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid"),
     *             @OA\Property(property="errors", type="object", example={"email": {"The email field is required."}})
     *         )
     *     )
     * )
     *
     * @OA\Post(
     *      path="/admin/send/verification-code",
     *      operationId="admin/send-verification-code",
     *      summary="send verification code ",
     *      tags={"Admin", "Admin - Auth"},
     *      security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *      security={{"lmsAuth": {}}},
     *      @OA\Parameter(
     *         name="type",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"forget_password","update_email","register","family_phone_number_register","update_family_phone_number"} ,
     *         )
     *     ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="send verification-code",
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="email", type="string", example="yosofbayan75@gmail.com"),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully sent",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="The given data was invalid"),
     *              @OA\Property(property="errors", type="object", example={"email": {"The email field is required."}})
     *          )
     *      )
     *  )
     */
    public function sendVerificationCode(SendVerificationCodeRequest $request)
    {
        try {
            $this->authService->sendVerificationCode($request);
            return success(__('messages.verification_code_sent_successfully'));
        } catch (\Exception $e) {
            return error($e->getMessage(), null, $e->getCode());
        }
    }


    /**
     * @OA\Post(
     *      path="/profile/update",
     *      operationId="updateProfile",
     *      tags={"App", "App - Auth"},
     *      security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *      summary="Update Profile data",
     *      description="Update user profile with the provided information",
     *      @OA\RequestBody(
     *          required=true,
     *          description="User data",
     *              @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *              @OA\Property(property="family_phone_number", type="string", example="1234567890"),
     *              @OA\Property(property="username", type="string", example="johndoe"),
     *              @OA\Property(property="first_name", type="string", example="john doe"),
     *              @OA\Property(property="last_name", type="string", example="john doe"),
     *              @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *              @OA\Property(property="password", type="string", example="password"),
     *              @OA\Property(property="password_confirmation", type="string", example="password"),
     *              @OA\Property(property="old_password", type="string", example="password"),
     *              @OA\Property(property="phone_number", type="string", example="1234567890"),
     *              @OA\Property(property="_method", type="string", example="PUT"),
     *              @OA\Property(property="city_id", type="integer"),
     *              @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="Image file to upload"
     *                 ),
     *          ),
     *   ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="User updated successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="User updated successfully"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *          )
     *      ),
     * )
     *
     * @OA\Post(
     *       path="/admin/profile/update",
     *       operationId="admin/updateProfile",
     *       tags={"Admin", "Admin - Auth"},
     *       security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *       summary="Update Profile data",
     *       description="Update Admin profile with the provided information",
     *       @OA\RequestBody(
     *           required=true,
     *           description="Admin data",
     *              *         @OA\MediaType(
     *               mediaType="multipart/form-data",
     *               @OA\Schema(
     *               @OA\Property(property="family_phone_number", type="string", example="1234567890"),
     *               @OA\Property(property="username", type="string", example="johndoe"),
     *               @OA\Property(property="first_name", type="string", example="john doe"),
     *               @OA\Property(property="last_name", type="string", example="john doe"),
     *               @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *               @OA\Property(property="password", type="string", example="password"),
     *               @OA\Property(property="password_confirmation", type="string", example="password"),
     *               @OA\Property(property="old_password", type="string", example="password"),
     *               @OA\Property(property="phone_number", type="string", example="1234567890"),
     *               @OA\Property(property="_method", type="string", example="PUT"),
     *               @OA\Property(
     *                      property="image",
     *                      type="string",
     *                      format="binary",
     *                      description="Image file to upload"
     *                  ),
     *           ),
     *    ),
     *       ),
     *       @OA\Response(
     *           response=201,
     *           description="Admin updated successfully",
     *           @OA\JsonContent(
     *               @OA\Property(property="message", type="string", example="User updated successfully"),
     *           )
     *       ),
     *       @OA\Response(
     *           response=422,
     *           description="Validation error",
     *           @OA\JsonContent(
     *               @OA\Property(property="message", type="string", example="The given data was invalid."),
     *           )
     *       ),
     *  )
     */

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $data = $this->authService->updateProfile($request);
            return $data;
        } catch (\Exception $e) {
            return error($e->getMessage(), null, $e->getCode());
        }
    }


}
