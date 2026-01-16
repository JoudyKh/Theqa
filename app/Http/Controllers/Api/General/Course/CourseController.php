<?php

namespace App\Http\Controllers\Api\General\Course;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use App\Services\General\Course\CourseService;
use App\Http\Requests\Api\General\Courses\GetAllCoursesRequest;

class CourseController extends Controller
{
    public function __construct(protected CourseService $courseService)
    {
    }

    /**
     * @OA\Get(
     *       path="/auth/courses",
     *       operationId="general/courses-filter",
     *       summary="get my courses ",
     *       tags={"App", "App - Courses"},
     *      security={{ "bearerAuth": {} ,"lmsAuth": {}, "Accept": "application/json" }},
     *      @OA\Response(response=200, description="Successful operation"),
     *   )
     */
    public function myCourses(Request $request)
    {
        return success($this->courseService->getMine($request));
    }

    /**
     * @OA\Get(
     *     path="/courses",
     *     operationId="general/courses-get",
     *     summary="Get all courses",
     *     tags={"App", "App - Courses"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {}, "Accept": "application/json" }},
     *     @OA\Parameter(
     *         name="is_special",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={0, 1},
     *             example=1
     *         ),
     *         description="Filter courses by special status (1 or 0)"
     *     ),
     *     @OA\Parameter(
     *         name="student_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
     *         ),
     *         description="Filter courses by student id "
     *     ),
     *     @OA\Parameter(
     *         name="paginate",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={0, 1},
     *             example=1
     *         ),
     *         description="Paginate the results (1 to paginate, 0 to get all)"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index(GetAllCoursesRequest $request)
    {
        return success($this->courseService->getAll($request));
    }
}