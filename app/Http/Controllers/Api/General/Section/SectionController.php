<?php

namespace App\Http\Controllers\Api\General\Section;

use App\Models\Lesson;
use App\Models\Section;
use App\Traits\ConfigTrait;
use App\Constants\Constants;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\LessonResource;
use App\Http\Resources\Section\CourseResource;
use App\Http\Resources\Section\SectionResource;
use App\Services\General\Section\SectionService;
use App\Http\Requests\Api\General\Section\GetAllSectionRequest;

class SectionController extends Controller
{
    use ConfigTrait;
    public function __construct(protected SectionService $sectionService)
    {
        $this->initConfig();
    }

    /**
     * @OA\Get(
     *     path="/sections",
     *     operationId="app/super-sections",
     *     summary="Get sections data",
     *     tags={"App", "App - Sections"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {}, "Accept": "application/json" }},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filter by type",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"super", "book_section","courses"}
     *         )
     *     ),
     *           @OA\Parameter(
     *          name="search",
     *          in="query",
     *          description="Filter by name",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *      @OA\Parameter(
     *           name="teacher_id",
     *           in="query",
     *           description="Filter by teacher",
     *           required=false,
     *           @OA\Schema(
     *               type="integer",
     *           )
     *       ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent()
     *     ),
     * )
     *
     * @OA\Get(
     *     path="/admin/sections",
     *     operationId="admin/super-sections",
     *     summary="Get  sections data",
     *     tags={"Admin", "Admin - Sections"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {}, "Accept": "application/json" }},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filter by type",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"super", "book_section"}
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Filter by name",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *            name="teacher_id",
     *            in="query",
     *            description="Filter by teacher",
     *            required=false,
     *            @OA\Schema(
     *                type="integer",
     *            )
     *        ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent()
     *     ),
     * )
     *
     * @OA\Get(
     *     path="/sections/{parentSection}",
     *     operationId="app/sections",
     *     summary="Get sections data",
     *     tags={"App", "App - Sections"},
     *     @OA\Parameter(
     *         name="parentSection",
     *         in="path",
     *         description="Pass the parent section id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     * 
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filter by type",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"super", "book_section" , "courses" , "course_sections" , "book_sub_section"}
     *         )
     *     ),
     * 
     *     security={{ "bearerAuth": {} ,"lmsAuth": {}, "Accept": "application/json" }},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent()
     *     ),
     * )
     *
     * @OA\Get(
     *     path="/admin/sections/{parentSection}",
     *     operationId="admin/sections",
     *     summary="Get sections data",
     *     tags={"Admin", "Admin - Sections"},
     *     @OA\Parameter(
     *         name="parentSection",
     *         in="path",
     *         description="Pass the parent section id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filter by type",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"super", "book_section" , "courses" , "course_sections" , "book_sub_section"}
     *         )
     *     ),
     *     security={{ "bearerAuth": {} ,"lmsAuth": {}, "Accept": "application/json" }},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent()
     *     ),
     * )
     */

    public function index(Section $parentSection = null, GetAllSectionRequest $request)
    {
        return $this->sectionService->getAll($parentSection, $request);
    }

    /**
     * @OA\Get(
     *      path="/sections/detail/{section_id}",
     *      operationId="app/section",
     *      summary="get section data ",
     *      tags={"App", "App - Sections"},
     *      @OA\Parameter(
     *         name="subSections",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={0, 1},
     *             example=1
     *         )
     *      ),
     *       @OA\Parameter(
     *      name="section_id",
     *      in="path",
     *      description="pass the section ",
     *      required=true,
     *      @OA\Schema(
     *          type="integer"
     *      )
     *       ),
     *     security={{ "bearerAuth": {} ,"lmsAuth": {}, "Accept": "application/json" }},
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent()),
     *  ),
     *
     * @OA\Get(
     *     path="/admin/sections/detail/{section_id}",
     *     operationId="admin/section",
     *     summary="get section data ",
     *     tags={"Admin", "Admin - Sections"},
     *     @OA\Parameter(
     *         name="subSections",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={0, 1},
     *             example=1
     *         )
     *      ),
     *      @OA\Parameter(
     *     name="section_id",
     *     in="path",
     *     description="pass the section ",
     *     required=true,
     *     @OA\Schema(
     *         type="integer"
     *     )
     *      ),
     *    security={{ "bearerAuth": {} ,"lmsAuth": {}, "Accept": "application/json" }},
     *    @OA\Response(response=200, description="Successful operation", @OA\JsonContent()),
     * )
     */
    public function show(Section $section)
    {
        try {
            return $this->sectionService->show($section);
        } catch (\Throwable $th) {
            return error($th->getMessage() , [$th->getMessage()] , $th->getCode()) ;
        }
    }
}
