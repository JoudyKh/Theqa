<?php

namespace App\Http\Controllers\Api\General\Teacher;

use App\Models\User;
use App\Constants\Constants;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserRecourse;
use App\Http\Resources\TeacherResource;
use Illuminate\Pagination\AbstractPaginator;
use App\Services\General\Teacher\TeacherService;
use App\Http\Requests\Api\Admin\Teacher\StoreTeacherRequest;
use App\Http\Requests\Api\Admin\Teacher\UpdateTeacherRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Services\Admin\Teacher\TeacherService as AdminTeacherService;

class TeacherController extends Controller
{
    public function __construct(protected TeacherService $teacherService)
    {
    }

    /**
     *
     * @OA\Get(
     *      path="/teachers",
     *      tags={"App", "App - Teachers"},
     *      summary="Retrieve a list of teachers",
     *      security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="List of teachers",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/TeacherResource")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid request parameters"
     *      ),
     *      @OA\Header(
     *          header="accept",
     *          description="return json",
     *          @OA\Schema(
     *              type="string",
     *              example="application/json"
     *          )
     *      )
     *  )
     *
     * @OA\Get(
     *     path="/admin/teachers",
     *     tags={"Admin", "Admin - Teachers"},
     *     summary="Retrieve a list of offers",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }}, 
     *     @OA\Response(
     *         response=200,
     *         description="List of teachers",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/TeacherResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request parameters"
     *     ),
     *     @OA\Header(
     *         header="accept",
     *         description="return json",
     *         @OA\Schema(
     *             type="string",
     *             example="application/json"
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        return $this->teacherService->getAll(Constants::TEACHER_ROLE, $request->trash, true);
    }

    /**
     * @OA\Get(
     *     path="/admin/teachers/{user}",
     *     tags={"Admin", "Admin - Teachers"},
     *     summary="Retrieve a specific teacher",
     *     description="Retrieve a specific teacher by user ID. Returns an error if the user is not a teacher.",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID of the teacher to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TeacherResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found or not a teacher",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The user is not a teacher")
     *         )
     *     )
     * )
     * 
     *     @OA\Get(
     *     path="/teachers/{user}",
     *     tags={"General", "General - Teachers" , "App" , "App - Teachers"},
     *     summary="Retrieve a specific teacher",
     *     description="Retrieve a specific teacher by user ID. Returns an error if the user is not a teacher.",
     *     security={{ "bearerAuth": {} ,"lmsAuth": {} }},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="ID of the teacher to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TeacherResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found or not a teacher",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The user is not a teacher")
     *         )
     *     )
     * )
     */
    public function show(User $teacher)
    {
        try {       
            $this->teacherService->validateRole($teacher, Constants::TEACHER_ROLE);
            return success(TeacherResource::make($teacher->load(['teacherCourses' , 'images'])));
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }
}
