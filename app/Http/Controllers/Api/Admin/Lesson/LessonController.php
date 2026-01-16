<?php

namespace App\Http\Controllers\Api\Admin\Lesson;

use App\Models\Lesson;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\LessonResource;
use App\Services\Admin\Exam\ExamService;
use App\Http\Requests\Api\Admin\Lesson\StoreLessonRequest;
use App\Http\Requests\Api\Admin\Lesson\UpdateLessonRequest;
use App\Services\Admin\Lesson\LessonService as AdminLessonService;

class LessonController extends Controller
{
    public function __construct(
        protected AdminLessonService $adminLessonService
    ) {
    }

    /**
     * @OA\Post(
     *     path="/admin/sections/{parentSection}/lessons",
     *     summary="Store a new lesson",
     *     tags={"Admin", "Admin - Lesson"},
     *     security={{"bearerAuth": {} ,"lmsAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/StoreLessonRequest")
     *         )
     *     ),
     *     @OA\Parameter(
     *     name="parentSection",
     *     in="path",
     *     description="pass the parent section id , dont pass it if its super section or book section  ",
     *     @OA\Schema(
     *         type="integer"
     *     )
     *      ),
     *     @OA\Response(
     *         response=201,
     *         description="Lesson created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/LessonResource")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation or processing error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Error message")
     *         )
     *     )
     * )
     */

    public function store($parentSection, StoreLessonRequest $request)
    {
        try {
            $lesson = $this->adminLessonService->storeTransaction($parentSection , $request);
            return success(LessonResource::make($lesson->loadMissing(['files'])), 201);
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Post(
     *     path="/admin/sections/{parentSection}/lessons/{lesson}",
     *     summary="Update an existing lesson",
     *     tags={"Admin", "Admin - Lesson"},
     *     security={{"bearerAuth": {} ,"lmsAuth": {}}},
     *     @OA\Parameter(
     *     name="parentSection",
     *     in="path",
     *     description="pass the parent section id , dont pass it if its super section or book section  ",
     *     @OA\Schema(
     *         type="integer"
     *     )
     *      ),
     *     @OA\Parameter(
     *         name="lesson",
     *         in="path",
     *         description="ID of the lesson to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="_method",
     *         in="query",
     *         required=true,
     *         description="Override HTTP method",
     *         @OA\Schema(type="string", example="PUT")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/UpdateLessonRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lesson updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/LessonResource")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation or processing error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function update($parentSection, Lesson $lesson, UpdateLessonRequest $request)
    {
        try {
            $this->adminLessonService->updateTransaction($lesson, $request);
            return success(LessonResource::make($lesson->loadMissing(['files'])));
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }


    /**
     * @OA\Delete(
     *     path="/admin/sections/{parentSection}/lessons/{lesson}",
     *     summary="Delete a lesson",
     *     tags={"Admin", "Admin - Lesson"},
     *     security={{"bearerAuth": {} ,"lmsAuth": {}}},
     *     @OA\Parameter(
     *     name="parentSection",
     *     in="path",
     *     description="pass the parent section id , dont pass it if its super section or book section  ",
     *     @OA\Schema(
     *         type="integer"
     *     )
     *      ),
     *     @OA\Parameter(
     *         name="lesson",
     *         in="path",
     *         description="ID of the lesson to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="force",
     *         in="query",
     *         required=true,
     *         description="Whether to force delete the option",
     *         @OA\Schema(
     *             type="integer",
     *             enum={0,1} ,
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Lesson deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation or processing error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Error message")
     *         )
     *     )
     * )
     */

    public function delete($parentSection, Lesson $lesson, $force = null)
    {
        try {
            $this->adminLessonService->delete($lesson);
            return success();
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Patch(
     *     path="/admin/sections/{parentSection}/lessons/{lesson}/restore",
     *     summary="Restore a deleted lesson",
     *     tags={"Admin", "Admin - Lesson"},
     *     security={{"bearerAuth": {} ,"lmsAuth": {}}},
     *     @OA\Parameter(
     *     name="parentSection",
     *     in="path",
     *     description="pass the parent section id , dont pass it if its super section or book section  ",
     *     @OA\Schema(
     *         type="integer"
     *     )
     *      ),
     *     @OA\Parameter(
     *         name="lesson",
     *         in="path",
     *         description="ID of the lesson to restore",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lesson restored successfully",
     *         @OA\JsonContent(ref="#/components/schemas/LessonResource")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Lesson is not deleted or cannot be restored",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="not deleted")
     *         )
     *     )
     * )
     */
    public function restore($parentSection, Lesson $lesson)
    {
        if (!$lesson->trashed()) {
            return error('not deleted', 'not deleted', 422);
        }
        $lesson->restore();
        return success(LessonResource::make($lesson));
    }
}
