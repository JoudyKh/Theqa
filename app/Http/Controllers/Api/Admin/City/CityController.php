<?php

namespace App\Http\Controllers\Api\Admin\City;

use App\Models\City;
use App\Models\Governorate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Http\Resources\GovernorateResource;
use App\Http\Requests\Api\Admin\City\StoreCityRequest;
use App\Http\Requests\Api\Admin\City\UpdateCityRequest;
use App\Services\Admin\City\CityService as AdminCityService;

class CityController extends Controller
{
    public function __construct(protected AdminCityService $cityService)
    {
    }

    /**
     * @OA\Get(
     *     path="/admin/governorates/{governorate_id}/cities",
     *     tags={"Admin", "Admin - City"},
     *     summary="Retrieve a list of cities",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="governorate_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
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
     *                 @OA\Items(ref="#/components/schemas/CityResource")
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
    public function index(Governorate $governorate, Request $request)
    {
        return $this->cityService->getAll($governorate, $request->trash);
    }

    /**
     * @OA\Get(
     *     path="/admin/governorates/{governorate_id}/cities/{city}",
     *     summary="Get details of a specific city (Admin)",
     *     tags={"Admin", "Admin - City"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="governorate_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="city",
     *         in="path",
     *         required=true,
     *         description="ID of the city to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved exam details",
     *         @OA\JsonContent(ref="#/components/schemas/CityResource")
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
    public function show(Governorate $governorate, City $city)
    {
        return success(CityResource::make(
            $city->loadCount(['students'])
        ) , 200 , [
            'governorate' => GovernorateResource::make($governorate) ,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/admin/governorates/{governorate_id}/cities",
     *     tags={"Admin" , "Admin - City"},
     *     summary="Create a new city",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="governorate_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/StoreCityRequest") ,
     *         )
     *      ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CityResource")
     *     )
     * )
     */
    public function store(Governorate $governorate, StoreCityRequest $request)
    {
        try {
            $city = $this->cityService->store($governorate, $request->validated());
            return success(CityResource::make($city), 201);
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Post(
     *     path="/admin/governorates/{governorate_id}/cities/{city_id}",
     *     tags={"Admin", "Admin - City"},
     *     summary="Update an existing city (simulated PUT request)",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="city_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *    @OA\Parameter(
     *         name="governorate_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
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
     *             @OA\Schema(ref="#/components/schemas/StoreCityRequest") 
     *         )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CityResource")
     *     )
     * )
     */
    public function update(Governorate $governorate, City $city, UpdateCityRequest $request)
    {
        try {
            $this->cityService->update($city, $request->validated());
            return success(CityResource::make($city));
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Delete(
     *     path="/admin/governorates/{governorate_id}/cities/{city_id}",
     *     tags={"Admin", "Admin - City"},
     *     summary="Delete an city",
     *     @OA\Parameter(
     *         name="city_id",
     *         in="path",
     *         description="ID of the city to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="governorate_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
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
    public function delete(Governorate $governorate, City $city, $force = null)
    {
        try {
            return $this->cityService->delete($city, request()->boolean('force'));
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }
    /**
     * @OA\Patch(
     *     path="/admin/governorates/{governorate_id}/cities/{city_id}/restore",
     *     tags={"Admin", "Admin - City"},
     *     summary="Restore a soft-deleted city",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="city_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="governorate_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
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

    public function restore(Governorate $governorate, City $city)
    {
        if (!$city->trashed()) {
            return error('not deleted', 'not deleted', 422);
        }
        $city->restore();
        return success(CityResource::make($city));
    }
}
