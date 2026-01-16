<?php

namespace App\Services\General\Lesson;

use Carbon\Carbon;
use App\Models\Exam;
use App\Models\User;
use App\Models\Lesson;
use App\Models\Section;
use App\DTOs\ExamResultDto;
use App\Models\StudentExam;
use App\Constants\Constants;
use Illuminate\Http\Request;
use App\Models\LessonStudent;
use App\Models\SectionStudent;
use App\Models\CertificateRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ExamResource;
use App\Http\Resources\LessonResource;
use App\Http\Resources\Section\CourseResource;
use App\Http\Resources\Section\SectionResource;
use App\Http\Resources\CertificateRequestResource;
use App\Services\General\Storage\File\FileService;
use App\Services\App\StudentExam\ExamResultService;
use App\Http\Requests\Api\General\Lesson\GetLessonRequest;

class LessonService
{
    public function __construct(
        protected FileService $filesService,
        protected ExamResultService $examResultService
    ) {
    }

    public function getFreeLessons(Section &$parentSection)
    {
        if ($parentSection->type != Constants::SECTION_TYPE_COURSES) {
            throw new \Exception('section is not courses , type error');
        }

        $lessons = Lesson::query();

        $lessons
            ->join('lesson_section', 'lessons.id', '=', 'lesson_section.lesson_id')
            ->join('sections', 'sections.id', '=', 'lesson_section.section_id')
            ->where('sections.parent_id', $parentSection->id)
            ->where('lessons.is_free', request()->boolean('is_free', true))
            ->select(['lessons.*', DB::raw('1 as is_open')]);

        return success(LessonResource::collection($lessons->paginate(config('app.pagination_limit'))));
    }

    public function getAll(Request &$request, string|int $parentSectionId)
    {
        $studentId = auth('sanctum')->id();
        $extraData = [];
        $lessons = Lesson::query();

        $lessons
            ->where('lessons.section_id', $parentSectionId)
            ->orderBy('lessons.lesson_order')
            ->orderByDesc('lessons.created_at');


        $section = Section::with([
            'teachers',
            'parentSection.parentSection'
        ])
            ->findOrFail($parentSectionId);

        $extraData['parent_section'] = CourseResource::make($section);



        $getFun = $request->boolean('get') ? 'get' : 'paginate';

        Lesson::loadLessonStudentArray();

        if (Section::isSubscribed($section->id)) {
            app()->instance('first_lesson_id', Section::getFirstLesson($section)?->id);

        }

        if ($request->boolean('separated')) {
            $lessonsQueryClone = clone $lessons;

            if ($getFun == 'get') {
                $data['free_lessons'] = $lessons->where('is_free', 1)->$getFun();
                $data['paid_lessons'] = $lessonsQueryClone->where('is_free', 0)->$getFun();
            } else {
                $data['free_lessons'] = $lessons->where('is_free', 1)->$getFun(config('app.pagination_limit'));
                $data['paid_lessons'] = $lessonsQueryClone->where('is_free', 0)->$getFun(config('app.pagination_limit'));
            }
        } else {
            if ($getFun == 'get') {
                $lessons = $lessons->$getFun();
            } else {
                $lessons = $lessons->$getFun(config('app.pagination_limit'));
            }
            $data = LessonResource::collection($lessons);
        }

        return success($data, 200, $extraData ?? []);
    }

    public function get(Section &$parentSection, Lesson &$lesson, GetLessonRequest &$request)
    {
        $extraData = [];
        $user = User::with(['roles'])->where('id', auth('sanctum')->id())->first();


        $parentSection->loadMissing(['parentSection.parentSection']);



        $extraData['parent_section'] = SectionResource::make($parentSection);


        $subscribed = Section::isSubscribed($parentSection->id, $user?->id);
        $lesson->loadMissing([
            'exam' => function ($exam) use ($lesson, $subscribed) {
                if (request()->is('*admin*') || $subscribed || $lesson->is_free) {
                    $exam->with('questions.options');
                }
            },
            'files'
        ]);
        if ($user?->hasRole(Constants::ADMIN_ROLE) or ($lesson->is_free and !$subscribed)) {
            app()->instance('allow_video_and_files', 1);
        }


        if ($user != null and $user->hasRole(Constants::STUDENT_ROLE)) {

            //check curr lesson status

            $lessonStudent = LessonStudent::where([
                'student_id' => $user->id,
                'lesson_id' => $lesson->id,
            ])->first();

            if ($lessonStudent) {
                app()->instance('allow_video_and_files', 1);

            }

            //check curr lesson exam status

            $exam = $lesson->exam;

            if ($exam) {
                $studentExam = StudentExam::where([
                    'student_id' => $user->id,
                    'exam_id' => $exam->id,
                ])
                    ->orderByDesc('created_at')
                    ->first();

                if ($studentExam?->degree) {
                    $resultDto = $this->getResultDtoForStudent($user, $exam);

                    if ($resultDto) {
                        app()->instance('examResultDto', $resultDto->toArray());
                    }

                } elseif ($studentExam and !$studentExam?->end_date and $studentExam?->start_date) {
                    app()->instance('is_solving', true);
                    app()->instance('start_date', $studentExam->start_date);
                    app()->instance('curr_date', now()->toDateTimeString());
                    app()->instance('remaining_time', Carbon::now()->diffInSeconds(Carbon::parse($studentExam->start_date)->addMinutes($exam->minutes)));
                }
            }

        }//end of (if user is student)

        $lesson->select(['lessons.*', DB::raw($parentSection->id . ' as parent_section_id')]);

        if (request()->is('*admin*')) {
            $lessons = Lesson::where('section_id', $lesson->section_id)
                ->where('id', '!=', $lesson->id)
                ->select(['id', 'lesson_order', 'section_id', 'name'])
                ->get();

            $extraData['extra_lessons'] = $lessons;

        }




        return success(LessonResource::make($lesson), 200, $extraData ?? []);
    }

    public function getResultDtoForStudent(User &$student, ?Exam $exam = null, $last = true): ?ExamResultDto
    {
        //i am student here

        if (!$exam)
            return null;

        $result = [];

        $student_exam = StudentExam::with(['student_answers'])->where([
            'student_id' => $student->id,
            'exam_id' => $exam->id,
        ])
            ->orderBy('created_at', $last ? 'desc' : 'asc')
            ->first();

        //i didnt try to solve the lesson_section
        if ($student_exam == null) {
            return null;
        }

        $resultDto = $this->examResultService->resultFromExam($student_exam, $exam);

        return $resultDto;
    }
}
