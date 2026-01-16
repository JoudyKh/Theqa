<?php

namespace App\Services\App\SectionStudent;

use App\Models\Exam;
use App\Models\User;
use App\Models\Lesson;
use App\Models\Section;
use App\Models\StudentExam;
use App\Models\PurchaseCode;
use App\Models\LessonStudent;
use App\Models\SectionStudent;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Section\SectionResource;
use App\Services\App\StudentExam\StudentExamService;
use App\Services\App\LessonStudent\LessonStudentService;
use App\Http\Requests\Api\App\SectionStudent\StoreSectionStudentRequest;
use App\Http\Requests\Api\App\SectionStudent\OpenNextSectionStudentRequest;

class SectionStudentService
{
    public function __construct(
        protected LessonStudentService $lessonStudentService
    ) {
    }

    public function openNext(Section &$parentSection, OpenNextSectionStudentRequest &$request)
    {
        return success();
    }

    public function store(StoreSectionStudentRequest &$request)
    {
        return DB::transaction(function () use (&$request) {

            $pivotExtraData = [];
            if (!$request->boolean('course_is_free')) {
                $code = PurchaseCode::where('code', $request->get('purchase_code'))->firstOrFail();
                $pivotExtraData['purchase_code_id'] = $code->id;
                $code->decrement('usage_limit');
            }

            $student = auth('sanctum')->user();
            $student->studentCourses()->attach($request->get('section_id'), $pivotExtraData);


            return true;
        });
    }

    public function myCourses()
    {
        $student = User::where('id', auth('sanctum')->id())->first();
        $courses = $student->studentCourses()
            ->orderBy('section_student.created_at', request()->query('orderBy', 'desc'));
        return SectionResource::collection($courses->paginate(config('app.pagination_limit')));
    }

    public function openNextSection(string|int|Section $currSection)
    {
        //todo optimize

        if (is_string($currSection) or is_int($currSection)) {
            $currSection = Section::where('sections.id', $currSection)->first();
        }

        $nextSection = Section::where('parent_id', $currSection->parent_id)
            ->where('id', '>', $currSection->id)
            ->first();

        if (!$nextSection) {
            return -1;
        }

        return DB::transaction(function () use (&$nextSection) {

            SectionStudent::create([
                'student_id' => auth('sanctum')->id(),
                'section_id' => $nextSection->id,
            ]);


            return $nextSection->id;
        });
    }
}
