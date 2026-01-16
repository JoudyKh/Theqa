<?php

namespace App\Http\Controllers\Api\Admin\Offer;

use App\Models\Offer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\OfferResource;
use App\Http\Requests\Api\Admin\Offer\StoreOfferRequest;
use App\Http\Requests\Api\Admin\Offer\UpdateOfferRequest;
use App\Services\Admin\Offer\OfferService as AdminOfferService;

class OfferController extends Controller
{
    public function __construct(protected AdminOfferService $offerService)
    {
    }

    /**
     * @OA\Get(
     *     path="/admin/offers",
     *     tags={"Admin", "Admin - Offer"},
     *     summary="Retrieve a list of offers",
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
     *                 @OA\Items(ref="#/components/schemas/OfferResource")
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
        return $this->offerService->getAll($request->trash);
    }

    /**
     * @OA\Get(
     *     path="/admin/offers/{offer}",
     *     summary="Get details of a specific offer (Admin)",
     *     tags={"Admin", "Admin - Offer"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="offer",
     *         in="path",
     *         required=true,
     *         description="ID of the offer to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved exam details",
     *         @OA\JsonContent(ref="#/components/schemas/OfferResource")
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
    public function show(Offer $offer)
    {
        return success(OfferResource::make($offer));
    }

    /**
     * @OA\Post(
     *     path="/admin/offers",
     *     tags={"Admin" , "Admin - Offer"},
     *     summary="Create a new offer",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/StoreOfferRequest") ,
     *         )
     *      ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OfferResource")
     *     )
     * )
     */
    public function store(StoreOfferRequest $request)
    {
        try {
            $offer = $this->offerService->store($request->validated());
            return success(OfferResource::make($offer), 201);
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Post(
     *     path="/admin/offers/{id}",
     *     tags={"Admin", "Admin - Offer"},
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
     *             @OA\Schema(ref="#/components/schemas/StoreOfferRequest") 
     *         )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OfferResource")
     *     )
     * )
     */
    public function update(Offer $offer, UpdateOfferRequest $request)
    {
        try {
            $this->offerService->update($offer, $request->validated());
            return success(OfferResource::make($offer));
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Delete(
     *     path="/admin/offers/{id}",
     *     tags={"Admin", "Admin - Offer"},
     *     summary="Delete an offer",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the offer to delete",
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
    public function delete(Offer $offer, $force = null)
    {
        try {
            $this->offerService->delete($offer, request()->boolean('force'));
            return success();
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }
    /**
     * @OA\Patch(
     *     path="/admin/offers/{id}/restore",
     *     tags={"Admin", "Admin - Offer"},
     *     summary="Restore a soft-deleted offer",
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

    public function restore(Offer $offer)
    {
        if (!$offer->trashed()) {
            return error('not deleted', 'not deleted', 422);
        }
        $offer->restore();
        return success(OfferResource::make($offer));
    }
}
