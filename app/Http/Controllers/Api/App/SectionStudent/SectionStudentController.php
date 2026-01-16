<?php

namespace App\Http\Controllers\Api\App\SectionStudent;

use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\SectionStudentResource;
use App\Http\Requests\Api\App\SectionStudent\StoreSectionStudentRequest;
use App\Http\Requests\Api\App\SectionStudent\OpenNextSectionStudentRequest;
use App\Services\App\SectionStudent\SectionStudentService as AppSectionStudentService;

class SectionStudentController extends Controller
{
    public function __construct(
        protected AppSectionStudentService $appSectionStudentService
    )
    {
    }

    /**
     * @OA\Post(
     *     path="/course-student",
     *     summary="Enroll a student in a course",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     tags={"App - BuyCourse"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/StoreSectionStudentRequest") ,
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="string"
     *             )
     *         )
     *     )
     * )
     */
    public function store(StoreSectionStudentRequest $request)
    {
        try {
            $this->appSectionStudentService->store($request);
            return success(['success' => true], 201);
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/sections/{section_id}/open",
     *     summary="Enroll a student in a course",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     tags={"App - Sections" , "App"},
     *     @OA\Parameter(
     *         name="section_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="string"
     *             )
     *         )
     *     )
     * )
     */
    public function open(Section &$section , OpenNextSectionStudentRequest $request)
    {
        try {
            return $this->appSectionStudentService->openNext($section , $request);
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], 400);
        }
    }
}