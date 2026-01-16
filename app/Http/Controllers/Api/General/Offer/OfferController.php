<?php

namespace App\Http\Controllers\Api\General\Offer;

use App\Models\Offer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\OfferResource;
use App\Services\General\Offer\OfferService as GeneralOfferService;

class OfferController extends Controller
{
    public function __construct(protected GeneralOfferService $offerService){} 

    /**
     * @OA\Get(
     *     path="/offers",
     *     tags={"General", "General - Offer"},
     *     summary="Retrieve a list of offers",
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
    public function index()
    {
        return $this->offerService->getAll() ;
    }


    /**
     * @OA\Get(
     *     path="/offers/{offer}",
     *     summary="Get details of a specific offer (Admin)",
     *     tags={"General", "General - Offer"},
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
}
