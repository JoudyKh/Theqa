<?php

namespace App\Http\Controllers\Api\General\Student;

use App\Models\User;
use App\Models\Governorate;
use App\Constants\Constants;
use App\Services\Admin\Exam\ExamService;
use App\Services\App\StudentExam\StudentExamService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\StudentResource;
use App\Http\Resources\GovernorateResource;
use Illuminate\Pagination\AbstractPaginator;
use App\Services\General\Student\StudentService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StudentController extends Controller
{
    public function __construct(protected StudentService $studentService)
    {
    }

    /**
     *
     * @OA\Get(
     *      path="/students",
     *      tags={"App", "App - Students"},
     *      summary="Retrieve a list of students",
     *      security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *      @OA\Parameter(
     *         name="top_students",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={0, 1},
     *             example=0
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="List of students",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/StudentResource")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid request parameters"
     *      ),
     *      @OA\Header(
     *          header="accept",
     *          description="return json",
     *          @OA\Schema(
     *              type="string",
     *              example="application/json"
     *          )
     *      )
     *  )
     *
     * @OA\Get(
     *     path="/admin/students",
     *     tags={"Admin", "Admin - Students"},
     *     summary="Retrieve a list of students",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {}}},
     *    @OA\Parameter(
     *         name="order_by_devices_count",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={0, 1},
     *             example=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="filters[devices_count]",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={0,1,2,3,4,5,6,7,8,9,10},
     *             example=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="filters[is_banned]",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={0,1},
     *             example=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="filters[is_active]",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={0,1},
     *             example=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="top_students",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={0, 1},
     *             example=0
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="filters[phone]",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="filters[name]",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="filters[city_id]",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="filters[governorate_id]",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort_by[total_avg]",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string" ,
     *             enum={"asc","desc"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of students",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/StudentResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request parameters"
     *     ),
     *     @OA\Header(
     *         header="accept",
     *         description="return json",
     *         @OA\Schema(
     *             type="string",
     *             example="application/json"
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        return $this->studentService->getAll(Constants::STUDENT_ROLE, $request->trash, true);
    }

    /**
     * @OA\Get(
     *     path="/admin/students/{user}",
     *     tags={"Admin", "Admin - Students"},
     *     summary="Retrieve a specific student",
     *     description="Retrieve a specific student by user ID. Returns an error if the user is not a student.",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID of the student to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/StudentResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found or not a student",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The user is not a student")
     *         )
     *     )
     * )
     */
    public function show($studentId)
    {
        $extraData = [];
        try {

            $topStudentQuery = User::withTopStudentsQuery()->toRawSql();

            $student = User::fromQuery($topStudentQuery)
                ->where('id', $studentId)
                ->firstOrFail()
                ?->load(['devices', 'images', 'roles']);

            $this->studentService->validateRole($student, Constants::STUDENT_ROLE);

            return success(StudentResource::make($student), 200, $extraData);
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }
}
