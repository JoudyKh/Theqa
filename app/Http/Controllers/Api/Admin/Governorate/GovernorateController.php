<?php

namespace App\Http\Controllers\Api\Admin\Governorate;

use App\Models\Governorate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\GovernorateResource;
use App\Http\Requests\Api\Admin\Governorate\StoreGovernorateRequest;
use App\Http\Requests\Api\Admin\Governorate\UpdateGovernorateRequest;
use App\Services\Admin\Governorate\GovernorateService as AdminGovernorateService;

class GovernorateController extends Controller
{
    public function __construct(protected AdminGovernorateService $governorateService)
    {
    }

    /**
     * @OA\Get(
     *     path="/admin/governorates",
     *     tags={"Admin", "Admin - Governorate"},
     *     summary="Retrieve a list of governorates",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="get",
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
    public function index(Request $request)
    {
        return $this->governorateService->getAll($request->trash);
    }

    /**
     * @OA\Get(
     *     path="/admin/governorates/{governorate}",
     *     summary="Get details of a specific governorate (Admin)",
     *     tags={"Admin", "Admin - Governorate"},
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
        return success(GovernorateResource::make(
            $governorate->loadMissing([
                'cities' => function ($query) {
                    $query->withCount('students');
                },
            ])->loadCount('student_count') 
        ));
    }

    /**
     * @OA\Post(
     *     path="/admin/governorates",
     *     tags={"Admin" , "Admin - Governorate"},
     *     summary="Create a new governorate",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/StoreGovernorateRequest") ,
     *         )
     *      ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/GovernorateResource")
     *     )
     * )
     */
    public function store(StoreGovernorateRequest $request)
    {
        try {
            $governorate = $this->governorateService->store($request->validated());
            return success(GovernorateResource::make($governorate->loadMissing(['cities'])), 201);
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Post(
     *     path="/admin/governorates/{id}",
     *     tags={"Admin", "Admin - Governorate"},
     *     summary="Update an existing governorate (simulated PUT request)",
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
     *             @OA\Schema(ref="#/components/schemas/StoreGovernorateRequest") 
     *         )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/GovernorateResource")
     *     )
     * )
     */
    public function update(Governorate $governorate, UpdateGovernorateRequest $request)
    {
        try {
            $this->governorateService->update($governorate, $request->validated());
            return success(GovernorateResource::make($governorate->loadMissing([
                'cities' => function ($query) {
                    $query->withCount('students');
                }
            ])));
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Delete(
     *     path="/admin/governorates/{id}",
     *     tags={"Admin", "Admin - Governorate"},
     *     summary="Delete an governorate",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the governorate to delete",
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
    public function destroy(Governorate $governorate, $force = null)
    {
        try {
            return $this->governorateService->delete($governorate, request()->boolean('force'));
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }
    /**
     * @OA\Patch(
     *     path="/admin/governorates/{id}/restore",
     *     tags={"Admin", "Admin - Governorate"},
     *     summary="Restore a soft-deleted governorate",
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

    public function restore(Governorate $governorate)
    {
        if (!$governorate->trashed()) {
            return error('not deleted', 'not deleted', 422);
        }
        $governorate->restore();
        return success(GovernorateResource::make($governorate));
    }
}
