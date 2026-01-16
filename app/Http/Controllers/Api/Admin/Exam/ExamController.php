<?php

namespace App\Http\Controllers\Api\Admin\Exam;

use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\ExamResource;
use App\Services\Admin\Exam\ExamService;
use App\Http\Requests\Api\Admin\Exam\CloneExamRequest;
use App\Http\Requests\Api\Admin\Exam\StoreExamRequest;
use App\Http\Requests\Api\Admin\Exam\UpdateExamRequest;
use App\Http\Requests\Api\Admin\Exam\GetAllExamsRequest;
use App\Http\Requests\Api\Admin\Exam\BulkDeleteExamRequest;
use App\Http\Requests\Api\Admin\Exam\UpdateExamModelRequest;

class ExamController extends Controller
{
    public function __construct(protected ExamService $examService)
    {
    }



    /**
     *
     *    * @OA\Get(
     *     path="/admin/exams/{exam}/students",
     *     summary="Get exam results",
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

    public function examResults(Exam $exam)
    {
        return success($this->examService->getExamStudents($exam));
    }

    /**
     * @OA\Post(
     *     path="/admin/clone/exams",
     *     summary="Store a new exam",
     *     tags={"Admin" , "Admin - Exams"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Site information data",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *         @OA\Schema(ref="#/components/schemas/CloneExamRequest"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully created exam",
     *         @OA\JsonContent(ref="#/components/schemas/ExamResource")
     *     ),
     * ),
     */
    public function clone(CloneExamRequest $request)
    {
        try {
            return $this->examService->clone($request);
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Post(
     *     path="/admin/exams",
     *     summary="Store a new exam",
     *     tags={"Admin" , "Admin - Exams"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Site information data",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *         @OA\Schema(ref="#/components/schemas/StoreExamRequest"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully created exam",
     *         @OA\JsonContent(ref="#/components/schemas/ExamResource")
     *     ),
     * ),
     *
     *    @OA\Post(
     *     path="/exams/generate",
     *     summary="Store a new exam",
     *     tags={"App" , "App - Exams"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Site information data",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *         @OA\Schema(ref="#/components/schemas/GenerateExamRequest"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully created exam",
     *         @OA\JsonContent(ref="#/components/schemas/ExamResource")
     *     ),
     * ),
     */
    public function store(StoreExamRequest $request)
    {
        try {
            $exam = $this->examService->storeTransaction($request);
            $exam->refresh();
            return success(ExamResource::make($exam->load('questions.options')), 201);
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }
    /**
     *
     * @OA\Post(
     *     path="/admin/exams/{exam}",
     *     summary="Update an existing exam (General)",
     *     tags={"Admin", "Admin - Exams"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="exam",
     *         in="path",
     *         required=true,
     *         description="ID of the exam to update",
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
     *         @OA\Schema(ref="#/components/schemas/UpdateExamRequest"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully updated exam",
     *         @OA\JsonContent(ref="#/components/schemas/ExamResource")
     *     ),
     * ),
     */
    public function update(Exam $exam, UpdateExamRequest $request)
    {
        try {
            $this->examService->updateTransaction($exam, $request);
            return success(ExamResource::make($exam->load('questions.options')));
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }
    /**
     *  * @OA\Delete(
     *     path="/admin/exams/{exam}",
     *     summary="Delete an exam",
     *     tags={"Admin", "Admin - Exams"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="exam",
     *         in="path",
     *         required=true,
     *         description="ID of the exam to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *          name="force",
     *          in="query",
     *          description="If 1, performs a force delete; if 0, performs a soft delete; if not provided, defaults to soft delete",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              enum={0, 1},
     *              example=1
     *          )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully deleted exam",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Exam deleted successfully.")
     *         )
     *     ),
     * ),
     *
     */
    public function delete(Exam $exam, $force = null)
    {
        try {
            //the force from parame not working
            $this->examService->delete($exam, request()->boolean('force'));
            return success();
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Post(
     *     path="/admin/exams/{exam}/restore",
     *     summary="Restore a soft-deleted exam",
     *     tags={"Admin", "Admin - Exams"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="exam",
     *         in="path",
     *         required=true,
     *         description="ID of the exam to restore",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully restored exam",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/ExamResource"
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Exam not deleted or already restored",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="not deleted"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="code", type="integer", example=422)
     *         )
     *     )
     * )
     */
    public function restore(Exam $exam)
    {
        if (!$exam->trashed()) {
            return error('not deleted', 'not deleted', 422);
        }
        $exam->restore();
        return success(ExamResource::make($exam));
    }

    /**
     * @OA\Post(
     *     path="/admin/exams/bulk-delete",
     *     tags={"Admin", "Admin - Exams"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/BulkDeleteExamRequest")
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
    public function bulkDelete(BulkDeleteExamRequest $request)
    {
        try {
            $this->examService->bulkDelete($request);
            return success();
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }
}
