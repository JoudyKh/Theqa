<?php

namespace App\Http\Controllers\Api\Admin\TopStudent;

use App\Models\TopStudent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TopStudentResource;
use App\Http\Requests\Api\Admin\TopStudent\StoreTopStudentRequest;
use App\Http\Requests\Api\Admin\TopStudent\UpdateTopStudentRequest;
use App\Services\Admin\TopStudent\TopStudentService as AdminTopStudentService;

class TopStudentController extends Controller
{
    public function __construct(protected AdminTopStudentService $topStudentService)
    {
    }

    /**
     * @OA\Get(
     *     path="/admin/top-students",
     *     tags={"Admin", "Admin - TopStudent"},
     *     summary="Retrieve a list of top_students",
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
     *                 @OA\Items(ref="#/components/schemas/TopStudentResource")
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
        return $this->topStudentService->getAll($request->trash);
    }

    /**
     * @OA\Get(
     *     path="/admin/top-students/{top_student}",
     *     summary="Get details of a specific top_student (Admin)",
     *     tags={"Admin", "Admin - TopStudent"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="top_student",
     *         in="path",
     *         required=true,
     *         description="ID of the top_student to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved exam details",
     *         @OA\JsonContent(ref="#/components/schemas/TopStudentResource")
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
    public function show(TopStudent $top_student)
    {
        return success(TopStudentResource::make($top_student));
    }

    /**
     * @OA\Post(
     *     path="/admin/top-students",
     *     tags={"Admin" , "Admin - TopStudent"},
     *     summary="Create a new top_student",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/StoreTopStudentRequest") ,
     *         )
     *      ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TopStudentResource")
     *     )
     * )
     */
    public function store(StoreTopStudentRequest $request)
    {
        try {
            $top_student = $this->topStudentService->store($request->validated());
            return success(TopStudentResource::make($top_student), 201);
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Post(
     *     path="/admin/top-students/{id}",
     *     tags={"Admin", "Admin - TopStudent"},
     *     summary="Update an existing top_student (simulated PUT request)",
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
     *             @OA\Schema(ref="#/components/schemas/StoreTopStudentRequest") 
     *         )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TopStudentResource")
     *     )
     * )
     */
    public function update(TopStudent $top_student, UpdateTopStudentRequest $request)
    {
        try {
            $this->topStudentService->update($top_student, $request->validated());
            return success(TopStudentResource::make($top_student));
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Delete(
     *     path="/admin/top-students/{id}",
     *     tags={"Admin", "Admin - TopStudent"},
     *     summary="Delete an top_student",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the top_student to delete",
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
    public function delete(TopStudent $top_student, $force = null)
    {
        try {
            $this->topStudentService->delete($top_student, request()->boolean('force'));
            return success();
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }
    /**
     * @OA\Patch(
     *     path="/admin/top-students/{id}/restore",
     *     tags={"Admin", "Admin - TopStudent"},
     *     summary="Restore a soft-deleted top_student",
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

    public function restore(TopStudent $top_student)
    {
        if (!$top_student->trashed()) {
            return error('not deleted', 'not deleted', 422);
        }
        $top_student->restore();
        return success(TopStudentResource::make($top_student));
    }
}
