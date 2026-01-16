<?php

namespace App\Http\Controllers\Api\General\Exam;

use App\Models\Exam;
use App\Http\Controllers\Controller;
use App\Http\Resources\ExamResource;
use App\Services\Admin\Exam\ExamService;
use App\Http\Requests\Api\Admin\Exam\GetAllExamsRequest;
use App\Http\Requests\Api\Admin\Exam\UpdateExamModelRequest;

class ExamController extends Controller
{
    public function __construct(protected ExamService $examService)
    {
    }

    /**
     * @OA\Get(
     *     path="/auth/exams",
     *     summary="Get a list of exams",
     *     tags={"App", "App - Exams"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="without_model",
     *         in="query",
     *         required=false,
     *         description="send 1 if you want exam with not section or lesson",
     *         @OA\Schema(
     *             type="integer",
     *             enum={1,0}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="generated_exams",
     *         in="query",
     *         required=false,
     *         description="get the student generated exams only",
     *         @OA\Schema(
     *             type="integer",
     *             enum={0, 1},
     *             example=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="section_id",
     *         in="query",
     *         required=false,
     *         description="section id ",
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="parent_section_id",
     *         in="query",
     *         required=false,
     *         description="parent_section id ",
     *         @OA\Schema(
     *             type="integer",
     *         )
     *    ),
     *    @OA\Parameter(
     *         name="exam_search",
     *         in="query",
     *         required=false,
     *         description="Search term to filter exams",
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *    @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"solved","failed"} ,
     *             nullable=true
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="statistics",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={1,0} ,
     *             nullable=true,
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="paginate",
     *         in="query",
     *         required=false,
     *         description="Include pagination in the result (0 or 1)",
     *         @OA\Schema(
     *             type="integer",
     *             enum={0, 1},
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved list of exams",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ExamResource")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     * )
     *
     */
    public function myExams(GetAllExamsRequest $request)
    {
        return $this->examService->getAll($request, auth('sanctum')->id());
    }

    /**
     * @OA\Get(
     *     path="/exams",
     *     summary="Get a list of exams (Admin)",
     *     tags={"App", "App - Exams"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="without_model",
     *         in="query",
     *         required=false,
     *         description="send 1 if you want exam with not section or lesson",
     *         @OA\Schema(
     *             type="integer",
     *             enum={1,0}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="section_id",
     *         in="query",
     *         required=false,
     *         description="section id ",
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="parent_section_id",
     *         in="query",
     *         required=false,
     *         description="parent_section id ",
     *         @OA\Schema(
     *             type="integer",
     *         )
     *    ),
     *    @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"solved","failed"} ,
     *             nullable=true
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="statistics",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={1,0} ,
     *             nullable=true,
     *         )
     *     ),
     *    @OA\Parameter(
     *         name="exam_search",
     *         in="query",
     *         required=false,
     *         description="Search term to filter exams",
     *         @OA\Schema(
     *             type="string",
     *             nullable=true,
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="paginate",
     *         in="query",
     *         required=false,
     *         description="Include pagination in the result (0 or 1)",
     *         @OA\Schema(
     *             type="integer",
     *             enum={0, 1},
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved list of exams",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ExamResource")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     * )
     *
     *
     *   * @OA\Get(
     *     path="/admin/exams",
     *     summary="Get a list of exams (Admin)",
     *     tags={"Admin", "Admin - Exams"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="is_solved",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={0,1} ,
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"solved","failed"} ,
     *             nullable=true
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="statistics",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={1,0} ,
     *             nullable=true,
     *         )
     *     ),
     *    @OA\Parameter(
     *         name="type",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="strin",
     *             enum={"GENERATED","ORIGINAL"} ,
     *             nullable=true,
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="section_id",
     *         in="query",
     *         required=false,
     *         description="section id ",
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="parent_section_id",
     *         in="query",
     *         required=false,
     *         description="parent_section id ",
     *         @OA\Schema(
     *             type="integer",
     *         )
     *    ),
     *    @OA\Parameter(
     *         name="exam_search",
     *         in="query",
     *         required=false,
     *         description="Search term to filter exams",
     *         @OA\Schema(
     *             type="string",
     *             nullable=true,
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="paginate",
     *         in="query",
     *         required=false,
     *         description="Include pagination in the result (0 or 1)",
     *         @OA\Schema(
     *             type="integer",
     *             enum={0, 1},
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved list of exams",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ExamResource")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     * )
     */

    public function index(GetAllExamsRequest $request)
    {
        return $this->examService->getAll($request);
    }

    //the students should see the exam with the lessons routes not here
    //so this should be protected

    /**
     * @OA\Get(
     *     path="/exams/{exam}",
     *     summary="Get details of a specific exam (Admin)",
     *     tags={"App", "App - Exams"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="exam",
     *         in="path",
     *         required=true,
     *         description="ID of the exam to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved exam details",
     *         @OA\JsonContent(ref="#/components/schemas/ExamResource")
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
     *
     *    * @OA\Get(
     *     path="/admin/exams/{exam}",
     *     summary="Get details of a specific exam (Admin)",
     *     tags={"Admin", "Admin - Exams"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="exam",
     *         in="path",
     *         required=true,
     *         description="ID of the exam to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved exam details",
     *         @OA\JsonContent(ref="#/components/schemas/ExamResource")
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

    public function show(Exam $exam)
    {
        return $this->examService->getExam($exam);
    }
}
