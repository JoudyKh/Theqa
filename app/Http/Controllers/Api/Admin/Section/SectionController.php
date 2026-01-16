<?php

namespace App\Http\Controllers\Api\Admin\Section;

use App\Models\Section;
use App\Constants\Constants;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Services\Admin\Section\SectionService;
use App\Http\Resources\Section\SectionResource;
use App\Http\Requests\Api\Admin\Section\OpenSectionRequest;
use App\Http\Requests\Api\Admin\Section\StoreSectionRequest;
use App\Http\Requests\Api\Admin\Section\CancelSectionRequest;
use App\Http\Requests\Api\Admin\Section\UpdateSectionRequest;

class SectionController extends Controller
{

    public function __construct(protected SectionService $sectionService)
    {
    }
    /**
     * @OA\Post(
     *      path="/admin/sections/cancel-for-student",
     *      operationId="cancel-open-section",
     *      tags={"Admin", "Admin - Sections"},
     *      security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *      summary="Store Section data",
     *      description="Store Section with the provided information",
     *      @OA\RequestBody(
     *          required=true,
     *          description="Section data",
     *            @OA\MediaType(
     *                  mediaType="multipart/form-data",
     *                  @OA\Schema(
     *                    @OA\Property(property="section_id", type="string", example=1),
     *                    @OA\Property(property="student_id", type="string", example=2),
     *                  )
     *            ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Section stored successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Section updated successfully"),
     *          )
     *      ),
     * )
     */
    public function cancel(CancelSectionRequest $request)
    {
        try {
            return $this->sectionService->cancel($request);
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }
    /**
     * @OA\Post(
     *      path="/admin/sections/open-for-student",
     *      operationId="post-open-section",
     *      tags={"Admin", "Admin - Sections"},
     *      security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *      summary="Store Section data",
     *      description="Store Section with the provided information",
     *      @OA\RequestBody(
     *          required=true,
     *          description="Section data",
     *            @OA\MediaType(
     *                  mediaType="multipart/form-data",
     *                  @OA\Schema(
     *                    @OA\Property(property="section_id", type="string", example=1),
     *                    @OA\Property(property="student_id", type="string", example=2),
     *                  )
     *            ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Section stored successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Section updated successfully"),
     *          )
     *      ),
     * )
     */
    public function open(OpenSectionRequest $request)
    {
        try {
            return $this->sectionService->open($request);
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }
    /**
     * @OA\Post(
     *       path="/admin/sections/store/{type}",
     *       operationId="post-super-section",
     *       tags={"Admin", "Admin - Sections"},
     *       security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *       @OA\Parameter(
     *       name="type",
     *       in="path",
     *       description="parents [book_section , super] , children [super -> courses or course_sections] , book_section [book_section -> book_sub_section] ",
     *       @OA\Schema(
     *           type="string"
     *       ),),
     *       summary="Store Super Section data",
     *       description="Store Super Section with the provided information",
     *       @OA\RequestBody(
     *           description="Section data",
     *               @OA\MediaType(
     *               mediaType="multipart/form-data",
     *               @OA\Schema(
     *               @OA\Property(property="name", type="string", example="section name "),
     *               @OA\Property(property="description", type="string", example="section description "),
     *               @OA\Property(
     *                      property="image",
     *                      type="string",
     *                      format="binary",
     *                      description="Image file to upload"
     *                  ),
     *           ),
     *    ),
     *       ),
     *       @OA\Response(
     *           response=201,
     *           description="Section stored successfully",
     *           @OA\JsonContent(
     *               @OA\Property(property="message", type="string", example="Section updated successfully"),
     *           )
     *       ),
     *       @OA\Response(
     *           response=422,
     *           description="Validation error",
     *           @OA\JsonContent(
     *               @OA\Property(property="message", type="string", example="The given data was invalid."),
     *           )
     *       ),
     *  ),
     * @OA\Post(
     *      path="/admin/sections/store/{type}/{parentSection}",
     *      operationId="post-store-section",
     *     tags={"Admin", "Admin - Sections"},
     *     @OA\Parameter(
     *     name="parentSection",
     *     in="path",
     *     description="pass the parent section id , dont pass it if its super section or book section  ",
     *     @OA\Schema(
     *         type="integer"
     *     )
     *      ),
     *     @OA\Parameter(
     *     name="type",
     *     in="path",
     *     description="pass it courses or course_sections or book_sub_section :) dont pass it if you want to add super section ",
     *      @OA\Schema(
     *          type="string"
     *        )
     *      ),
     *      security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *      summary="Store Section data",
     *      description="Store Section with the provided information",
     *      @OA\RequestBody(
     *          required=true,
     *          description="Section data",
     *              @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *  @OA\Property(
     *     property="is_special",
     *     type="integer",
     *     enum={1, 0},
     *     example=1
     * ),
     *              @OA\Property(property="name", type="string", example="section name "),
     *              @OA\Property(property="is_free", type="integer", example="1"),
     *              @OA\Property(property="price", type="integer", example="100"),
     *              @OA\Property(property="discount", type="integer", example="100"),
     *              @OA\Property(property="description", type="string", example="lorem upsum"),
     *              @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="Image file to upload"
     *                 ),
     *              @OA\Property(
     *                      property="intro_video",
     *                      type="string",
     *                      format="binary",
     *                      description="Image file to upload"
     *                  ),
     *          ),
     *   ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Section stored successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Section updated successfully"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *          )
     *      ),
     * )
     */

    public function store(StoreSectionRequest $request, string $type, Section $parentSection = null)
    {
        try {
            $section = $this->sectionService->storeTransaction($request, $type, $parentSection);
            return success(SectionResource::make($section), 201);
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }

    /**
     * @OA\Post(
     *      path="/admin/sections/{id}",
     *      operationId="store-section",
     *     tags={"Admin", "Admin - Sections"},
     *     @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="section id to update ",
     *     required=false,
     *     @OA\Schema(
     *         type="integer"
     *     )
     *      ),
     *      security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *      summary="Update Section data",
     *      description="Update Section with the provided information",
     *      @OA\RequestBody(
     *          required=true,
     *          description="Section data",
     *              @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *
     *              @OA\Property(property="name", type="string", example="section name "),
     *              @OA\Property(property="description", type="string", example="lorem upsum"),
     *              @OA\Property(property="is_free", type="integer", example="1"),
     *                    @OA\Property(
     *     property="is_special",
     *     type="integer",
     *     enum={1, 0},
     *     example=1
     * ),
     *              @OA\Property(property="price", type="integer", example="100"),
     *              @OA\Property(property="discount", type="integer", example="100"),
     *              @OA\Property(property="_method", type="string", example="PUT"),
     *              @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="Image file to upload"
     *                 ),
     *              @OA\Property(
     *                     property="intro_video",
     *                     type="string",
     *                     format="binary",
     *                     description="Image file to upload"
     *                 ),
     *          ),
     *   ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Section updated successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Section updated successfully"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *          ),
     *      ),
     * )
     */
    public function update(UpdateSectionRequest $request, Section $section)
    {
        try {
            $this->sectionService->updateTransaction($request, $section);
            return success(SectionResource::make($section));
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }


    /**
     * @OA\Delete(
     *     path="/admin/sections/{id}",
     *     summary="Delete a Section",
     *     tags={"Admin", "Admin - Sections"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the section to delete",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Section deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Section not found"
     *     )
     * )
     */


    public function destroy(Section $section)
    {
        $section->deleteOrFail();
        return success();
    }
}
