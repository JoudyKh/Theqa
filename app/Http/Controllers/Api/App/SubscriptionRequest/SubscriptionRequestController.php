<?php

namespace App\Http\Controllers\Api\App\SubscriptionRequest;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\StudentExamResource;
use App\Http\Resources\SubscriptionRequestResource;
use App\Services\App\SubscriptionRequest\SubscriptionRequestService;
use App\Http\Requests\Api\App\SubscriptionRequest\CheckCouponRequest;
use App\Http\Requests\Api\App\SubscriptionRequest\CreateSubscriptionReqRequest;

class SubscriptionRequestController extends Controller
{
    public function __construct(protected SubscriptionRequestService $subscriptionRequestService)
    {

    }

    /**
     * @OA\Get(
     *     path="/student/subs-requests",
     *     tags={"App", "App - Student Subscription Requests"},
     *     summary="Retrieve a list of Subscription Requests",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="sting",
     *             enum={"pending", "rejected", "accepted" },
     *             example="pending"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of Subscription Requests",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/SubscriptionRequestResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request parameters"
     *     ),
     *     @OA\Header(
     *         header="accept",
     *         description="return json",
     *         @OA\Schema(
     *             type="string",
     *             example="application/json"
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $subscriptionRequests = $this->subscriptionRequestService->getUserRequests($request);
            return success(SubscriptionRequestResource::collection($subscriptionRequests));
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Post(
     *     path="/student/subs-requests",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     summary="Create a new Subscription Request",
     *     description="Create a new Subscription Request.",
     *     tags={"App", "App - Student Subscription Requests"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="The image file of the student"
     *                 ),
     *                 @OA\Property(
     *                     property="section_id",
     *                     type="integer",
     *                     description="The ID of the section."
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful creation",
     *         @OA\JsonContent(ref="#/components/schemas/SubscriptionRequestResource")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     )
     * )
     */
    public function store(CreateSubscriptionReqRequest $request)
    {
        try {
            $subscriptionRequest = $this->subscriptionRequestService->create($request);
            return success(SubscriptionRequestResource::make($subscriptionRequest), 201);
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Post(
     *     path="/student/subs-requests/coupon-check",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     summary="check coupon",
     *     description="Create a new Subscription Request.",
     *     tags={"App", "App - Student Subscription Requests"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="coupon",
     *                     type="string",
     *                     description="The coupon."
     *                 ),
     *                 @OA\Property(
     *                     property="section_id",
     *                     type="integer",
     *                     description="The ID of the section."
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful creation",
     *         @OA\JsonContent(ref="#/components/schemas/SubscriptionRequestResource")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     )
     * )
     */
    public function checkCoupon(CheckCouponRequest $request)
    {
        try { 
            return $this->subscriptionRequestService->checkCoupon($request);
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }
}
