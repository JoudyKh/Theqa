<?php

namespace App\Http\Controllers\Api\Admin\CourseTeacher;

use App\Models\User;
use App\Models\Section;
use App\Constants\Constants;
use App\Http\Controllers\Controller;
use App\Services\Admin\CourseTeacher\CourseTeacherService;
use App\Http\Requests\Api\Admin\CourseTeacher\CourseTeacherRequest;
use App\Http\Requests\Api\Admin\Section\Teacher\OpenSectionTeacherRequest;
use App\Http\Requests\Api\Admin\Section\Teacher\CancelSectionTeacherRequest;

class CourseTeacherController extends Controller
{
    public function __construct(protected CourseTeacherService $courseTeacherService){}
    /**
     * @OA\Post(
     *     path="/admin/sections/open-for-teacher",
     *     summary="Add a user(teacher) to a section (course)",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     tags={"Admin" , "Admin - Teachers" , "Admin - Sections"},
     *     @OA\RequestBody(
     *          required=true,
     *          description="Section data",
     *            @OA\MediaType(
     *                  mediaType="multipart/form-data",
     *                  @OA\Schema(
     *                    @OA\Property(property="section_id", type="string", example=1),
     *                    @OA\Property(property="teacher_id", type="string", example=2),
     *                  )
     *            ),
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Teacher successfully assigned to the course.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Teacher assigned to course successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation or business logic error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Error message.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Server error message.")
     *         )
     *     )
     * )
     */
    public function store(OpenSectionTeacherRequest &$request)
    {
        try {
            return $this->courseTeacherService->store($request) ;
        } catch (\Throwable $th) {
            return error($th->getMessage(),[$th->getMessage()],400);
        }
    }
    /**
     * @OA\Post(
     *     path="/admin/sections/cancel-for-teacher",
     *     summary="Remove a user(teacher) from a section (course)",
     *     tags={"Admin" , "Admin - Teachers" , "Admin - Sections"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\RequestBody(
     *          required=true,
     *          description="Section data",
     *            @OA\MediaType(
     *                  mediaType="multipart/form-data",
     *                  @OA\Schema(
     *                    @OA\Property(property="section_id", type="string", example=1),
     *                    @OA\Property(property="teacher_id", type="string", example=2),
     *                  )
     *            ),
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Teacher successfully removed from the course.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Teacher removed from course successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation or business logic error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Error message.")
     *         )
     *     ), 
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Server error message.")
     *         )
     *     )
     * )
     */
    public function delete(CancelSectionTeacherRequest &$request)
    {
        try {
            return $this->courseTeacherService->delete($request) ;
        } catch (\Throwable $th) {
            return error($th->getMessage(),[$th->getMessage()],400);
        }
    }
}
