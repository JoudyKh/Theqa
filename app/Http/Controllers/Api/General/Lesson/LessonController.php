<?php

namespace App\Http\Controllers\Api\General\Lesson;

use App\Models\File;
use App\Models\Lesson;
use App\Models\Section;
use App\Constants\Constants;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\LessonResource;
use App\Services\General\Lesson\LessonService;
use App\Http\Resources\Section\SectionResource;
use App\Http\Requests\Api\General\Lesson\GetLessonRequest;

class LessonController extends Controller
{
    public function __construct(protected LessonService $generalLessonService)
    {
    }

    /**
     * @OA\Get(
     *     path="/sections/{parentSection}/free-lessons",
     *     summary="Get all free lessons in course",
     *     tags={"App", "App - Lesson"},
     *     security={{"bearerAuth": {} ,"lmsAuth": {}}},
     *     @OA\Parameter(
     *     name="parentSection",
     *     in="path",
     *     description="pass the parent section id ",
     *     @OA\Schema(
     *         type="integer"
     *     )
     *      ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid parameter or error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Error message")
     *         ),
     *     )
     * )
     **/
    public function courseIndex(Section $parentSection)
    {
        try {
            return $this->generalLessonService->getFreeLessons($parentSection) ;
        } catch (\Throwable $th) {
            return error($th->getMessage(),[$th->getMessage()],$th->getCode()) ;
        }
    }

    /**
     * @OA\Get(
     *     path="/sections/{parentSection}/lessons",
     *     summary="Get all lessons",
     *     tags={"App", "App - Lesson"},
     *     security={{"bearerAuth": {} ,"lmsAuth": {}}},
     *     @OA\Parameter(
     *     name="parentSection",
     *     in="path",
     *     description="pass the parent section id ",
     *     @OA\Schema(
     *         type="integer"
     *     )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="List of lessons",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/LessonResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid parameter or error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Error message")
     *         ),
     *     )
     * ),
     * @OA\Get(
     *     path="/admin/sections/{parentSection}/lessons",
     *     summary="Get all lessons",
     *     tags={"Admin", "Admin - Lesson"},
     *     security={{"bearerAuth": {} ,"lmsAuth": {}}},
     *     @OA\Parameter(
     *     name="parentSection",
     *     in="path",
     *     description="pass the parent section id , pass 'all' if you want all lessons  ",
     *     @OA\Schema(
     *         type="integer"
     *     )
     *      ),
     *     @OA\Parameter(
     *         name="trash",
     *         in="query",
     *         description="Filter by trash status (1 for trashed, 0 for not trashed)",
     *         required=false,
     *         @OA\Schema(type="integer", enum={0, 1})
     *     ),
     *     @OA\Parameter(
     *         name="separated",
     *         in="query",
     *         description="get the lessons grouped by free or close ",
     *         required=false,
     *         @OA\Schema(type="integer", enum={0, 1})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of lessons",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/LessonResource")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid parameter or error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Error message")
     *         ),
     *     )
     * )
     */
    public function index($parentSection, Request $request)
    {
        return $this->generalLessonService->getAll($request, $parentSection);
    }

    /**
     *  @OA\Get(
     *     path="/sections/{parentSection}/lessons/{lesson}",
     *     summary="Get a specific lesson",
     *     tags={"App", "App - Lesson"},
     *     security={{"bearerAuth": {} ,"lmsAuth": {}}},
     *     @OA\Parameter(
     *     name="parentSection",
     *     in="path",
     *     description="pass the parent section id  ",
     *     @OA\Schema(
     *         type="integer"
     *     )
     *      ),
     *     @OA\Parameter(
     *         name="lesson",
     *         in="path",
     *         description="ID of the lesson to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lesson details",
     *         @OA\JsonContent(ref="#/components/schemas/LessonResource"),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Lesson not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Lesson not found")
     *         ),
     *     ),
     * ),
     * @OA\Get(
     *     path="/admin/sections/{parentSection}/lessons/{lesson}",
     *     summary="Get a specific lesson",
     *     tags={"Admin", "Admin - Lesson"},
     *     security={{"bearerAuth": {} ,"lmsAuth": {}}},
     *     @OA\Parameter(
     *     name="parentSection",
     *     in="path",
     *     description="pass the parent section id ",
     *     @OA\Schema(
     *         type="integer"
     *     )
     *      ),
     *     @OA\Parameter(
     *         name="lesson",
     *         in="path",
     *         description="ID of the lesson to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lesson details",
     *         @OA\JsonContent(ref="#/components/schemas/LessonResource"),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Lesson not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Lesson not found")
     *         ),
     *     ),
     * )
     */

    public function show(Section $parentSection, Lesson $lesson , GetLessonRequest &$request)
    {
        return $this->generalLessonService->get($parentSection, $lesson , $request);
    }


    /**
     * @OA\Get(
     *     path="/sections/{parentSection}/lessons/{id}/files/{file}/download",
     *     summary="Download a specific lesson.file",
     *     tags={"App" , "App - Lesson"},
     *     @OA\Parameter(
     *     name="parentSection",
     *     in="path",
     *     description="pass the parent section id , dont pass it if its super section or book section  ",
     *     @OA\Schema(
     *         type="integer"
     *     )
     *      ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the book to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found",
     *     )
     * )
     *
     * @OA\Get(
     *     path="/admin/sections/{parentSection}/lessons/{lesson}/files/{file}/download",
     *     summary="dDownload a specific book",
     *     tags={"Admin" , "Admin - Lesson"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *     name="parentSection",
     *     in="path",
     *     description="pass the parent section id ",
     *     @OA\Schema(
     *         type="integer"
     *     )
     *      ),
     *     @OA\Parameter(
     *         name="lesson",
     *         in="path",
     *         required=true,
     *         description="ID of the lesson to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="file",
     *         in="path",
     *         required=true,
     *         description="ID of the file to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Details of the requested book",
     *         @OA\JsonContent(ref="#/components/schemas/BookResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found",
     *     )
     * )
     */
    public function download($parentSection, Lesson $lesson, File $file)
    {
        if ($file->model_id != $lesson->id and $file->model_type == Lesson::class) {
            return error('file not for this lesson', ['file not for this lesson'], 422);
        }
        $fileStoragePath = 'storage/' . $file->path;

        $filePath = public_path($fileStoragePath);

        if (!file_exists($filePath)) {
            return error('File not found.', ['File not found.'], 404);
        }
        return response()->download($filePath,$file->name);
    }
}
