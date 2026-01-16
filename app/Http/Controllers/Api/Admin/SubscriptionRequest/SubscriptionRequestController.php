<?php

namespace App\Http\Controllers\Api\Admin\SubscriptionRequest;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\SubscriptionRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\StudentExamResource;
use App\Http\Resources\SubscriptionRequestResource;
use App\Services\Admin\SubscriptionRequest\SubscriptionRequestService;
use App\Http\Requests\Api\App\SubscriptionRequest\CreateSubscriptionReqRequest;
use App\Http\Requests\Api\Admin\SubscriptionRequest\UpdateSubscriptionReqRequest;

class SubscriptionRequestController extends Controller
{
    public function __construct(protected SubscriptionRequestService $subscriptionRequestService)
    {
    }

    /**
     * @OA\Get(
     *     path="/admin/students/subs-requests",
     *     tags={"Admin", "Admin - Student Subscription Requests"},
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
            $subscriptionRequests = $this->subscriptionRequestService->getRequests($request);
            return success(SubscriptionRequestResource::collection($subscriptionRequests));
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }


    /**
     * @OA\Delete(
     *     path="/admin/students/subs-requests/{subscriptionRequest}",
     *     summary="Delete a subscriptionRequest",
     *     description="Deletes a subscriptionRequest. If `force` is specified, the student will be permanently deleted; otherwise, it will be soft deleted.",
     *     tags={"Admin", "Admin - Student Subscription Requests"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="subscriptionRequest",
     *         in="path",
     *         required=true,
     *         description="ID of the subscriptionRequest to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="force",
     *         in="query",
     *         description="If true, performs a force delete; if not provided, performs a soft delete",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={0,1} ,
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successfully deleted student",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad request, possibly due to invalid parameters",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid request")
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Student not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Student not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Server error")
     *         )
     *     )
     * )
     */
    public function delete(SubscriptionRequest $subscriptionRequest): JsonResponse
    {
        try {
            $this->subscriptionRequestService->delete($subscriptionRequest, request()->boolean('force'));
            return success();
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Post(
     *     path="/admin/students/subs-requests/{subscriptionRequest}/status",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     summary="manage Subscription Request status ",
     *     description="manage Subscription Request status ",
     *     tags={"Admin", "Admin - Student Subscription Requests"},
     *     @OA\Parameter(
     *          name="subscriptionRequest",
     *          in="path",
     *          required=true,
     *          description="ID of the subscriptionRequest ",
     *          @OA\Schema(type="integer")
     *      ),
     *     @OA\RequestBody(
     *        required=true,
     *          @OA\JsonContent(ref="#/components/schemas/UpdateSubscriptionReqRequest")
     *      ),
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
    public function manageStatus(SubscriptionRequest $subscriptionRequest, UpdateSubscriptionReqRequest $request)
    {
        try {
            return $this->subscriptionRequestService->manageStatus($subscriptionRequest, $request);
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

}
