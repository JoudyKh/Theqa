<?php

namespace App\Http\Controllers\Api\General\Exam;

use App\Models\Exam;
use App\Http\Controllers\Controller;
use App\Http\Resources\ExamResource;
use App\Services\Admin\Exam\ExamService;
use App\Http\Requests\Api\Admin\Exam\GetAllExamsRequest;
use App\Http\Requests\Api\Admin\Exam\UpdateExamModelRequest;
use Illuminate\Http\Request;

class QuestionsController extends Controller
{
    public function __construct(protected ExamService $examService)
    {
    }

    /**
     * @OA\Get(
     *     path="/questions",
     *     summary="Get a list of exams",
     *     tags={"App", "App - Exams"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="page_number",
     *         in="query",
     *         required=false,
     *         description="page number",
     *         @OA\Schema(
     *             type="integer",
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
     *     @OA\Parameter(
     *         name="subject_id",
     *         in="query",
     *         required=false,
     *         description="Include pagination in the result (0 or 1)",
     *         @OA\Schema(
     *             type="integer",
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
    public function index(Request $request)
    {
        return success($this->examService->getQuestions($request->page_number, $request->paginate, $request->subject_id));
    }


    /**
     * @OA\Get(
     *     path="/pages",
     *     summary="Get a list of exams",
     *     tags={"App", "App - Exams"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="subject_id",
     *         in="query",
     *         required=false,
     *         description="Include pagination in the result (0 or 1)",
     *         @OA\Schema(
     *             type="integer",
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
    public function pages(Request $request)
    {
        return success($this->examService->getQuestions(null, null, $request->subject_id, true));
    }

}