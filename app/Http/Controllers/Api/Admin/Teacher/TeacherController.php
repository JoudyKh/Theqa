<?php

namespace App\Http\Controllers\Api\Admin\Teacher;

use App\Models\User;
use App\Constants\Constants;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserRecourse;
use App\Http\Resources\TeacherResource;
use App\Services\Admin\Teacher\TeacherService;
use App\Http\Requests\Api\Admin\Teacher\StoreTeacherRequest;
use App\Http\Requests\Api\Admin\Teacher\UpdateTeacherRequest;

class TeacherController extends Controller
{
    public function __construct(protected TeacherService $teacherService)
    {
    }

    /**
     * @OA\Post(
     *     path="/admin/teachers",
     *     tags={"Admin", "Admin - Teachers"},
     *     summary="Store a new teacher",
     *     description="Create a new teacher record. Returns the created teacher resource.",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/StoreTeacherRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Teacher created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/TeacherResource")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation failed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred")
     *         )
     *     )
     * )
     */
    public function store(StoreTeacherRequest $request): JsonResponse|TeacherResource
    {
        try {
            $teacher = $this->teacherService->store($request, Constants::TEACHER_ROLE);
            return TeacherResource::make($teacher);
        } catch (\Throwable $th) {
            return error($th->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/admin/teachers/{user}",
     *     tags={"Admin", "Admin - Teachers"},
     *     summary="Update an existing teacher",
     *     description="Update details of an existing teacher. Returns the updated teacher resource.",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="_method",
     *         in="query",
     *         required=true,
     *         description="Override HTTP method",
     *         @OA\Schema(type="string", example="PUT")
     *     ),
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID of the teacher to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/UpdateTeacherRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Teacher updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/TeacherResource")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation failed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Teacher not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Teacher not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred")
     *         )
     *     )
     * )
     */
    public function update(User $teacher, UpdateTeacherRequest $request): JsonResponse|TeacherResource
    {
        try {
            $this->teacherService->update($teacher, $request->validated());
            return TeacherResource::make($teacher->load(['teacherCourses']));
        } catch (\Throwable $th) {
            return error($th->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/teachers/{teacher}",
     *     summary="Delete a student",
     *     description="Deletes a teacher. If `force` is specified, the teacher will be permanently deleted; otherwise, it will be soft deleted.",
     *     operationId="deleteTeacher",
     *     tags={"Admin", "Admin - Teachers"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="teacher",
     *         in="path",
     *         required=true,
     *         description="ID of the teacher to delete",
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
     *         description="Successfully deleted teacher",
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
     *             @OA\Property(property="error", type="string", example="Teacher not found")
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
    public function delete(User $teacher, $force = null): JsonResponse
    {
        try {
            $this->teacherService->delete($teacher, request()->boolean('force'));
            return success();
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }

    }

    public function restore()
    {
        
    }
}
