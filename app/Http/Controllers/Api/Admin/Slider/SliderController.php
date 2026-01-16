<?php

namespace App\Http\Controllers\Api\Admin\Slider;

use App\Models\Slider;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\SliderResource;
use App\Http\Requests\Api\Admin\Slider\StoreSliderRequest;
use App\Http\Requests\Api\Admin\Slider\UpdateSliderRequest;
use App\Services\Admin\Slider\SliderService as AdminSliderService;

class SliderController extends Controller
{
    public function __construct(protected AdminSliderService $sliderService)
    {
    }

    /**
     * @OA\Get(
     *     path="/admin/sliders",
     *     tags={"Admin", "Admin - Slider"},
     *     summary="Retrieve a list of sliders",
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
     *    @OA\Parameter(
     *         name="type",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"hero","locations","our_features"},
     *             nullable=true,
     *             example="hero"
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
        return $this->sliderService->getAll($request->trash);
    }

    /**
     * @OA\Get(
     *     path="/admin/sliders/{slider}",
     *     summary="Get details of a specific slider (Admin)",
     *     tags={"Admin", "Admin - Slider"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="slider",
     *         in="path",
     *         required=true,
     *         description="ID of the slider to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved exam details",
     *         @OA\JsonContent(ref="#/components/schemas/SliderResource")
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
    public function show(Slider $slider)
    {
        return success(SliderResource::make($slider));
    }

    /**
     * @OA\Post(
     *     path="/admin/sliders",
     *     tags={"Admin" , "Admin - Slider"},
     *     summary="Create a new slider",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/StoreSliderRequest") ,
     *         )
     *      ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SliderResource")
     *     )
     * )
     */
    public function store(StoreSliderRequest $request)
    {
        try {
            $slider = $this->sliderService->store($request);
            return success(SliderResource::make($slider), 201);
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Post(
     *     path="/admin/sliders/{id}",
     *     tags={"Admin", "Admin - Slider"},
     *     summary="Update an existing slider (simulated PUT request)",
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
     *             @OA\Schema(ref="#/components/schemas/StoreSliderRequest") 
     *         )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SliderResource")
     *     )
     * )
     */
    public function update(Slider $slider, UpdateSliderRequest $request)
    {
        try {
            $this->sliderService->update($slider, $request);
            return success(SliderResource::make($slider));
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Delete(
     *     path="/admin/sliders/{id}",
     *     tags={"Admin", "Admin - Slider"},
     *     summary="Delete an slider",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the slider to delete",
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
    public function delete(Slider $slider, $force = null)
    {
        try {
            $this->sliderService->delete($slider, request()->boolean('force'));
            return success();
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }
    /**
     * @OA\Patch(
     *     path="/admin/sliders/{id}/restore",
     *     tags={"Admin", "Admin - Slider"},
     *     summary="Restore a soft-deleted slider",
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

    public function restore(Slider $slider)
    {
        if (!$slider->trashed()) {
            return error('not deleted', 'not deleted', 422);
        }
        $slider->restore();
        return success(SliderResource::make($slider));
    }
}
