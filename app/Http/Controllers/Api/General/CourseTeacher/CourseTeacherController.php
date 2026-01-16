<?php

namespace App\Http\Controllers\Api\General\CourseTeacher;

use App\Models\User;
use App\Models\Section;
use App\Constants\Constants;
use App\Http\Controllers\Controller;
use App\Http\Resources\TeacherResource;
use App\Http\Resources\Section\CourseResource;
use App\Http\Resources\Section\SectionResource;

class CourseTeacherController extends Controller
{
    /**
     * @OA\Get(
     *     path="/admin/teachers/{teacher_id}/courses",
     *     summary="Get courses for a teacher",
     *     tags={"Admin", "Admin - Teachers"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="teacher_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the teacher"
     *     ),
     *    @OA\Header(
     *         header="Accept",
     *         description="The media type expected by the client",
     *         @OA\Schema(
     *             type="string",
     *             example="application/json"
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unauthorized - User is not a teacher",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="User is not a teacher")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found - Teacher not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Teacher not found")
     *         )
     *     )
     * ),
     * @OA\Get(
     *     path="/teachers/{teacher_id}/courses",
     *     summary="Get courses for a teacher",
     *     tags={"App", "App - Teachers" , "General" , "General - Teachers"},
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="teacher_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the teacher"
     *     ),
     *    @OA\Header(
     *         header="Accept",
     *         description="The media type expected by the client",
     *         @OA\Schema(
     *             type="string",
     *             example="application/json"
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unauthorized - User is not a teacher",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="User is not a teacher")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found - Teacher not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Teacher not found")
     *         )
     *     )
     * )
     */

    public function getCourses(User $teacher)
    {
        $courses = Section::where('sections.type' , Constants::SECTION_TYPE_COURSES)
        ->whereHas('teachers' , function($query)use(&$teacher){
            $query->where('users.id' , $teacher->id) ;
        })->withSubSectionLessonTimes()
        ->paginate(config('app.pagination_limit')) ;

        $extraData['teacher'] = TeacherResource::make($teacher->loadMissing(['images'])) ;
        return success(CourseResource::collection($courses) , 200 , $extraData);
    }
}