<?php

namespace App\Services\General\Course;

use App\Models\User;
use App\Models\Section;
use App\Constants\Constants;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use App\Http\Resources\Section\CourseResource;
use App\Http\Resources\Section\SectionResource;
use App\Http\Requests\Api\General\Courses\GetAllCoursesRequest;

class CourseService
{
    public function getMine(Request $request)
    {
        $user = User::with(['roles'])->where('id', auth('sanctum')->id())->first();

        $sections = Section::query();


        return CourseResource::collection($sections->paginate(config('app.pagination_limit')));
    }

    public function getAll(GetAllCoursesRequest &$request)
    {
        $courses = Section::where('sections.type', Constants::SECTION_TYPE_COURSES);

        if (request()->has('is_special')) {
            $courses->where('sections.is_special', request()->has('is_special'));
        }

        $courses->withSubSectionLessonTimes();

        if ($request->has('student_id')) {
            $courses->whereHas('students', function ($users) use ($request) {
                $users->where('users.id', $request->input('student_id'));
            });
        }

        $courses = request()->boolean('paginate') ?
            $courses->paginate(config('app.pagination_limit')) :
            $courses->get();

        return CourseResource::collection($courses);
    }
}
