<?php

namespace App\Http\Controllers\Api\General\Governorate;

use App\Models\Governorate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\GovernorateResource;
use App\Services\General\Governorate\GovernorateService as GeneralGovernorateService;

class GovernorateController extends Controller
{
    public function __construct(protected GeneralGovernorateService $governorateService){} 

    /**
     * @OA\Get(
     *     path="/governorates",
     *     tags={"General", "General - Governorate"},
     *     summary="Retrieve a list of governorates",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/GovernorateResource")
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
        return $this->governorateService->getAll() ;
    }


    /**
     * @OA\Get(
     *     path="/governorates/{governorate}",
     *     summary="Get details of a specific governorate (Admin)",
     *     tags={"General", "General - Governorate"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="governorate",
     *         in="path",
     *         required=true,
     *         description="ID of the governorate to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved exam details",
     *         @OA\JsonContent(ref="#/components/schemas/GovernorateResource")
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
    public function show(Governorate $governorate)
    {
        return success(GovernorateResource::make($governorate->loadMissing([
            'cities' => function($query){
                $query->withCount('students') ;
            }
        ])));
    }
}