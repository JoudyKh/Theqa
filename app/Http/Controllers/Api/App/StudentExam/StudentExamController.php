<?php

namespace App\Http\Controllers\Api\App\StudentExam;

use DB;
use App\Models\Exam;
use App\Models\Section;
use App\Models\StudentExam;
use App\Http\Controllers\Controller;
use App\Services\App\StudentExam\ExamResultService;
use App\Services\App\StudentExam\StudentExamService;
use App\Services\App\LessonStudent\LessonStudentService;
use App\Services\App\SectionStudent\SectionStudentService;
use App\Http\Requests\Api\App\StudentExam\StoreStudentExamRequest;
use App\Http\Requests\Api\App\StudentExam\CreateStudentExamRequest;

class StudentExamController extends Controller
{
    public function __construct(
        protected StudentExamService $studentExamService,
        protected ExamResultService $examResultService,
        protected LessonStudentService $lessonStudentService,
        protected SectionStudentService $sectionStudentService
    ) {
    }
    /**
     * @OA\Post(
     *     path="/student-exams/{exam}/create",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     summary="Create a new student exam",
     *     description="Create a new student exam.",
     *     tags={"App", "App - Student Exams"},
     *     @OA\Parameter(
     *         name="exam",
     *         in="path",
     *         required=true,
     *         description="ID of the exam to start",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful creation",
     *         @OA\JsonContent(ref="#/components/schemas/StudentExamResource")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     )
     * )
     */
    public function create(Exam $exam, CreateStudentExamRequest $request)
    {
        try {
            return $this->studentExamService->createTransaction($request, $exam);
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }
    /**
     * @OA\Post(
     *     path="/student-exams/{exam}/store",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     summary="Create a new student exam",
     *     description="Create a new student exam.",
     *     tags={"App", "App - Student Exams"},
     *     @OA\Parameter(
     *         name="exam",
     *         in="path",
     *         required=true,
     *         description="ID of the exam to start",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/StoreStudentExamRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true, description="Indicates if the operation was successful"),
     *             @OA\Property(property="data", type="object", description="Result of the operation")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */

    public function store(Exam $exam, StoreStudentExamRequest $request)
    {
        try {
            return $this->studentExamService->solve($exam, $request);
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }
    /**
     * @OA\Post(
     *     path="/exams/{exam}/open",
     *     operationId="openNextExamId",
     *     summary="open next exam",
     *     tags={"App", "App - Exams"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {}, "Accept": "application/json" }},
     *     @OA\Parameter(
     *         name="exam",
     *         in="path",
     *         description="ID of the exam",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Error message"
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="array",
     *                 @OA\Items(
     *                     type="string"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 properties={}
     *             )
     *         )
     *     )
     * )
     */
    public function open(Exam $exam)
    {
        $nextExamId = null;
        $nextSectionId = null;
        $exam->loadMissing(['questions']);

        $studentExam = StudentExam::where([
            'student_id' => auth('sanctum')->id(),
            'exam_id' => $exam->id,
        ])
            ->orderByDesc('created_at')
            ->first();

        if (!$studentExam and $exam->questions?->count() == 0 and Section::isSubscribed($exam->model_id)) {
            $studentExam = StudentExam::create([
                'student_id' => auth('sanctum')->id(),
                'exam_id' => $exam->id,
                'exam_degree' => $exam->degree ?? 100,
                'exam_pass_percentage' => $exam->pass_percentage
            ]);
        }

        if ($exam->questions?->count() > 0 and !StudentExam::isPassedTheExam($studentExam)) {
            throw new \Exception('curr exam is not solved');
        }

        try {
            $nextExamId = $this->studentExamService->openNextExam($exam);

            return success([
                'next_exam_id' => $nextExamId,
                'next_section_id' => $nextSectionId,
                'message' => $this->message($nextExamId, $nextSectionId),
            ]);

        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    public function message($nextExamId, $nextSectionId)
    {
        if ($nextSectionId === -1) {
            return __('messages.last_section');
        }
        if ($nextExamId === -1) {
            return __('messages.last_exam');
        }
        return __('messages.you_passed_the_exam');
    }
}
