<?php

namespace App\Http\Controllers\Api\Admin\Student;

use App\Models\User;
use App\Constants\Constants;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\StudentResource;
use App\Services\Admin\Student\StudentService;
use App\Http\Requests\Api\Admin\Student\StoreStudentRequest;
use App\Http\Requests\Api\Admin\Student\UpdateStudentRequest;
use App\Http\Requests\Api\Admin\Student\BulkDeleteStudentRequest;

class StudentController extends Controller
{
    public function __construct(protected StudentService $studentService)
    {
    }

    /**
     * @OA\Post(
     *     path="/admin/students",
     *     tags={"Admin", "Admin - Students"},
     *     summary="Store a new student",
     *     description="Create a new student record. Returns the created student resource.",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/StoreStudentRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Student created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/StudentResource")
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
    public function store(StoreStudentRequest $request): JsonResponse
    {
        try {
            $student = $this->studentService->store($request, Constants::STUDENT_ROLE);
            return success(StudentResource::make($student?->loadMissing(['images' , 'city'])));
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Post(
     *     path="/admin/students/{user}",
     *     tags={"Admin", "Admin - Students"},
     *     summary="Update an existing student",
     *     description="Update details of an existing student. Returns the updated student resource.",
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
     *         description="ID of the student to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/UpdateStudentRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Student updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/StudentResource")
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
     *         description="Student not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Student not found")
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
    public function update(User $student, UpdateStudentRequest $request): JsonResponse
    {
        try {
            $this->studentService->update($student, $request->validated());
            return success(StudentResource::make($student?->loadMissing(['images' , 'city'])));
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Delete(
     *     path="/students/{student}",
     *     summary="Delete a student",
     *     description="Deletes a student. If `force` is specified, the student will be permanently deleted; otherwise, it will be soft deleted.",
     *     operationId="deleteStudent",
     *     tags={"Admin", "Admin - Students"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="student",
     *         in="path",
     *         required=true,
     *         description="ID of the student to delete",
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
    public function delete(User $student, $force = null): JsonResponse
    {
        
        try {
            $this->studentService->delete($student, request()->boolean('force'));
            return success();
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Post(
     *     path="/admin/students/bulk-delete",
     *     tags={"Admin", "Admin - Students"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/BulkDeleteStudentRequest")
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
    public function bulkDelete(BulkDeleteStudentRequest $request)
    {
        try {
            $this->studentService->bulkDelete($request);
            return success();
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        } 
    }

    public function restore()
    {
        
    }
}
