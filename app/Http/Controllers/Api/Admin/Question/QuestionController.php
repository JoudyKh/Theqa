<?php

namespace App\Http\Controllers\Api\Admin\Question;

use App\Models\Exam;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\QuestionResource;
use App\Services\Admin\Question\QuestionService;
use App\Http\Requests\Api\Admin\Question\StoreQuestionRequest;
use App\Http\Requests\Api\Admin\Question\GetAllQuestionRequest;
use App\Http\Requests\Api\Admin\Question\UpdateQuestionRequest;

class QuestionController extends Controller
{
    public function __construct(protected QuestionService $questionService)
    {
    }
    /**
     * @OA\Get(
     *     path="/admin/exam/questions/search",
     *     summary="Get all questions",
     *     description="search for question using sub string.",
     *     tags={"Admin" , "Admin - Question"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="sub name to search",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of questions retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/QuestionResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     )
     * )
     */
    public function search()
    {
        //scout database driver dose not support query builder so i cant use it with the index
        return success($this->questionService->search());
    }
    /**
     * @OA\Get(
     *     path="/admin/exam/questions",
     *     summary="Get all questions",
     *     description="Retrieve a list of all questions with optional filtering and trash inclusion.",
     *     tags={"Admin" , "Admin - Question"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="trash",
     *         in="query",
     *         required=false,
     *         description="Include trashed questions",
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of questions retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/QuestionResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     )
     * )
     */
    public function index(GetAllQuestionRequest $request)
    {
        return success($this->questionService->getAll($request));
    }

    /**
     * @OA\Get(
     *     path="/admin/exam/questions/{question}",
     *     summary="Get details of a specific question (Admin)",
     *     tags={"Admin", "Admin - Question"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="question",
     *         in="path",
     *         required=true,
     *         description="ID of the question to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved question details",
     *         @OA\JsonContent(ref="#/components/schemas/QuestionResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="question not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="question not found."),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="code", type="integer", example=404)
     *         )
     *     ),
     * )
     * 
     */
    public function show(Question $question)
    {
        return success(QuestionResource::make($question)) ;
    }
    /**
     * @OA\Post(
     *     path="/admin/exam/questions",
     *     summary="Store a new question",
     *     tags={"Admin" , "Admin - Question"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Site information data",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *         @OA\Schema(ref="#/components/schemas/StoreQuestionRequest"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Question created successfully",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/QuestionResource"
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input or other errors",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid input"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function store(StoreQuestionRequest $request)
    {  
        try {
            $question = $this->questionService->storeTransaction($request);
            return QuestionResource::make($question);
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Post(
     *     path="/admin/exam/questions/{question}",
     *     summary="Update an existing question",
     *     tags={"Admin" , "Admin - Question"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="question",
     *         in="path",
     *         required=true,
     *         description="ID of the question to update",
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
     *         description="Site information data",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *         @OA\Schema(ref="#/components/schemas/UpdateQuestionRequest"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully updated question",
     *         @OA\JsonContent(ref="#/components/schemas/QuestionResource")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="code", type="integer", example=400)
     *         )
     *     )
     * )
     */
    public function update(Question $question, UpdateQuestionRequest $request)
    {        
        try {
            $this->questionService->updateTransaction($question, $request);
            return success(QuestionResource::make($question->load('options')));//i have to reload the options bucause of changes
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Delete(
     *     path="/admin/exam/questions/{question}",
     *     summary="Delete a question",
     *     tags={"Admin" , "Admin - Question"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="question",
     *         in="path",
     *         required=true,
     *         description="ID of the question to delete",
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
     *         description="Successfully deleted question",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Question deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="code", type="integer", example=400)
     *         )
     *     )
     * )
     */
    public function delete(Question $question, $force = null)
    {
        try {
            $this->questionService->delete($question, request()->boolean('force'));
            return success();
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Patch(
     *     path="/admin/exam/questions/{question}/restore",
     *     summary="Restore a soft-deleted question",
     *     tags={"Admin" , "Admin - Question"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="question",
     *         in="path",
     *         required=true,
     *         description="ID of the question to restore",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully restored question",
     *         @OA\JsonContent(ref="#/components/schemas/QuestionResource")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Question not deleted or already restored",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="not deleted"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="code", type="integer", example=422)
     *         )
     *     )
     * )
     */
    public function restore(Question $question)
    {
        if (!$question->trashed()) {
            return error('not deleted', 'not deleted', 422);
        }
        $question->restore();
        return success(QuestionResource::make($question));
    }
}
