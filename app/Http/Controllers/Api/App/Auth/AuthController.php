<?php

namespace App\Http\Controllers\Api\App\Auth;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\App\Auth\AuthService;
use App\Http\Requests\Api\App\Auth\SignUpRequest;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @OA\Post(
     *      path="/register",
     *      operationId="app/register",
     *      tags={"App", "App - Auth"},
     *      security={{"lmsAuth": {}, "Accept": "application/json" }},
     *      summary="register a new user",
     *      description="register a new user with the provided information",
     *      @OA\RequestBody(
     *          required=true,
     *          description="User data",
     *         @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     * 
     *              @OA\Property(property="phone_number_country_code", type="string", example="1234567890"),
     *              @OA\Property(property="family_phone_number_country_code", type="string", example="123456789"),
     * 
     *              @OA\Property(property="family_phone_number_verification_code", type="string", example="1234"),
     *              @OA\Property(property="verification_code", type="string", example="1234"),
     * 
     *              @OA\Property(property="username", type="string", example="admin"),
     * 
     *              @OA\Property(property="full_name", type="string", example="john doe"),
     *              @OA\Property(property="birth_date", type="string", example="2000-10-10"),
     *              @OA\Property(property="location", type="string", example="syria"),
     * 
     *              @OA\Property(property="first_name", type="string", example="john doe"),
     *              @OA\Property(property="last_name", type="string", example="john doe"),
     *              @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *              @OA\Property(property="password", type="string", example="password"),
     *              @OA\Property(property="family_phone_number", type="string", example="1234567890"),
     *              @OA\Property(property="phone_number", type="string", example="123456789"),
     *              @OA\Property(property="city_id", type="integer"),
     *              @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="Image file to upload"
     *                 ),
     *          ),
     *     ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="User created successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="User created successfully"),
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
     */
    
    public function register(SignUpRequest $request)
    {
        try {
            $data = DB::transaction(function()use(&$request){
                return $this->authService->register($request); 
            });
            return success($data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return error($e->getMessage(), null, $e->getCode());
        }
    }
}