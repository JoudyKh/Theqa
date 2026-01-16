<?php

namespace App\Http\Controllers\Api\App\LessonStudent;

use App\Models\Exam;
use App\Models\User;
use App\Models\Lesson;
use App\Models\Section;
use App\Constants\Constants;
use Illuminate\Http\Request;
use App\Models\LessonStudent;
use App\Http\Controllers\Controller;
use App\Services\App\LessonStudent\LessonStudentService;
use App\Services\App\SectionStudent\SectionStudentService;

class LessonStudentController extends Controller
{
    public function __construct(
        protected LessonStudentService $lessonStudentService,
        protected SectionStudentService $sectionStudentService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/auth/done-lessons",
     *     operationId="GetAllDoneLessons",
     *     summary="get all done lessons",
     *     tags={"App", "App - Lesson"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {}, "Accept": "application/json" }},
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
     *     )
     * )
     */
    public function doneLessons()
    {
        return success($this->lessonStudentService->getDoneLessons());
    }

    /**
     * @OA\Post(
     *     path="/sections/{section}/lessons/{lesson}/open",
     *     operationId="adminStoreSectionLesson",
     *     summary="Store a lesson for a section",
     *     tags={"App", "App - Lesson"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {}, "Accept": "application/json" }},
     *     @OA\Parameter(
     *         name="section",
     *         in="path",
     *         description="ID of the section",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="lesson",
     *         in="path",
     *         description="ID of the lesson",
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
     *     )
     * )
     */
    public function store(Section $parentSection, Lesson $lesson)
    {
        if ($parentSection->id != $lesson->section_id) {
            throw new \Exception('this lesson is not for this section ');
        }

        $lessonStudent = LessonStudent::where([
            'lesson_id' => $lesson->id,
            'student_id' => auth('sanctum')->id(),
        ])->first();

        //check first lesson , if subscribe , create or first lesson_student

        if (!$lessonStudent) {
            if (
                Section::isSubscribed($parentSection->id)
                and
                Section::getFirstLesson($parentSection)?->id == $lesson->id
            ) {
                $lessonStudent = LessonStudent::create([
                    'lesson_id' => $lesson->id,
                    'student_id' => auth('sanctum')->id(),
                ]);
            } else {
                throw new \Exception('curr lesson is not open');
            }
        }

        if (!Lesson::isPassedLesson($lesson)) {
            throw new \Exception('curr lesson is not solved');
        }

        $nextLessonId = null;
        $nextSectionId = null;

        try {
            $nextLessonId = $this->lessonStudentService->openNextLesson($lesson);


            return success([
                'next_lesson_id' => $nextLessonId,
                'next_section_id' => $nextSectionId,
                'message' => $this->message($nextLessonId, $nextSectionId),
            ]);

        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }
    public function message($nextLessonId, $nextSectionId)
    {
        if ($nextSectionId === -1) {
            return __('messages.last_section');
        }
        if ($nextLessonId === -1) {
            return __('messages.last_lesson');
        }
        return __('messages.you_passed_the_lesson');
    }
}
