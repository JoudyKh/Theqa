<?php

namespace App\Http\Controllers\Api\Admin\Coupon;

use App\Http\Requests\Api\Admin\Coupon\StoreCouponRequest;
use App\Http\Requests\Api\Admin\Coupon\UpdateCouponRequest;
use App\Http\Resources\CouponResource;
use App\Models\Coupon;
use App\Models\Offer;
use App\Services\Admin\Coupon\CouponService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\OfferResource;
use App\Http\Requests\Api\Admin\Offer\StoreOfferRequest;
use App\Http\Requests\Api\Admin\Offer\UpdateOfferRequest;
use App\Services\Admin\Offer\OfferService as AdminOfferService;

class CouponController extends Controller
{
    public function __construct(protected CouponService $couponService)
    {
    }

    /**
     * @OA\Get(
     *     path="/admin/coupons",
     *     tags={"Admin", "Admin - Coupons"},
     *     summary="Retrieve a list of coupons",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="trash",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={0, 1},
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/CouponResource")
     *             ),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request parameters"
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            return success($this->couponService->getAll($request->trash));
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }

    }

    /**
     * @OA\Get(
     *     path="/admin/coupons/{coupon}",
     *     summary="Get details of a specific coupon (Admin)",
     *     tags={"Admin", "Admin - Coupons"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="coupon",
     *         in="path",
     *         required=true,
     *         description="ID of the coupon to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved exam details",
     *         @OA\JsonContent(ref="#/components/schemas/CouponResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Exam not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Exam not found."),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="code", type="integer", example=404)
     *         )
     *     ),
     * )
     *
     */
    public function show(Coupon $coupon)
    {
        try {
            return success(CouponResource::make($coupon));
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Post(
     *     path="/admin/coupons",
     *     tags={"Admin", "Admin - Coupons"},
     *     summary="Create a new coupon",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/StoreCouponRequest") ,
     *         )
     *      ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CouponResource")
     *     )
     * )
     */
    public function store(StoreCouponRequest $request)
    {
        try {
            $offer = $this->couponService->store($request->validated());
            return success(CouponResource::make($offer), 201);
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Post(
     *     path="/admin/coupons/{id}",
     *     tags={"Admin", "Admin - Coupons"},
     *     summary="Update an existing offer (simulated PUT request)",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="_method",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", enum={"PUT"}, default="PUT")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/UpdateCouponRequest")
     *         )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OfferResource")
     *     )
     * )
     */
    public function update(Coupon $coupon, UpdateCouponRequest $request)
    {
        try {
            $this->couponService->update($coupon, $request->validated());
            return success(CouponResource::make($coupon));
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Delete(
     *     path="/admin/coupons/{id}",
     *     tags={"Admin", "Admin - Coupons"},
     *     summary="Delete an coupon",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the coupon to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="force",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={0,1} ,
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function delete(Coupon $coupon, $force = null)
    {
        try {
            $this->couponService->delete($coupon, request()->boolean('force'));
            return success();
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Patch(
     *     path="/admin/coupons/{id}/restore",
     *     tags={"Admin", "Admin - Coupons"},
     *     summary="Restore a soft-deleted Coupon",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
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
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */

    public function restore(Coupon $coupon)
    {
        if (!$coupon->trashed()) {
            return error('not deleted', 'not deleted', 422);
        }
        $coupon->restore();
        return success(CouponResource::make($coupon));
    }
}
