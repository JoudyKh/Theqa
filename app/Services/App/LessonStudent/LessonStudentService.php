<?php

namespace App\Services\App\LessonStudent;

use Exception;
use Carbon\Carbon;
use App\Models\Exam;
use App\Models\User;
use App\Models\Lesson;
use App\Models\Section;
use App\Models\StudentExam;
use App\Constants\Constants;
use App\Models\LessonStudent;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\LessonResource;
use Illuminate\Database\Eloquent\Model;

class LessonStudentService
{
    public function getDoneLessons()
    {
        $student_id = auth('sanctum')->id();
        $lessons = Lesson::whereHas('exam', function ($exam) use (&$student_id) {
            $exam->whereHas('studentExams', function ($studentExam) use (&$student_id) {
                $studentExam
                    ->where('student_id', $student_id)
                    ->whereNotNull('student_exams.degree');
            });
        });
        return LessonResource::collection($lessons->paginate(config('app.pagination_limit')));
    }

    public function store(Lesson|Model|string|int &$lessonId, string|int|null $studentId = null)
    {
        $studentId = $studentId ?? auth('sanctum')->id();

        if (($lessonId instanceof Lesson) or ($lessonId instanceof Model)) {
            $lessonId = $lessonId->id;
        }
        LessonStudent::createOrFirst([
            'student_id' => $studentId,
            'lesson_id' => $lessonId,
        ]);
    }

    public function openFirstLesson($section, $studentId = null, $depthSectionLevel = 0)
    {
        //todo depthSectionLevel is how many join i have to do to reach the exam

        $studentId = ($studentId ?? auth('sanctum')->id());
        $firstLesson = null;

        if (!($section instanceof Section))
            $section = Section::where('id', $section)->firstOrFail();



        if (!$firstLesson) {
            throw new Exception(__('messages.section_has_no_lessons'), 422);
        }
        $this->store($firstLesson, $studentId);

        return true;
    }

    public function openNextLesson(Lesson|int|string $lesson)
    {
        if (!($lesson instanceof Lesson)) {
            $lesson = Lesson::with(['exam.questions'])->findOrFail($lesson);
        } else {
            $lesson->loadMissing('exam.questions');
        }

        $student = User::
            with(['roles'])
            ->where('id', auth('sanctum')->id())
            ->whereHas('roles', function ($role) {
                $role->where('name', Constants::STUDENT_ROLE);
            })
            ->firstOrFail();

        $lessonStudent = LessonStudent::where([
            'lesson_id' => $lesson->id,
            'student_id' => $student->id,
        ])->first();

        if (!$lessonStudent) {
            throw new Exception('you didint open the curr lesson yet');
        }


        if (!Lesson::isPassedLesson($lesson, $student->id)) {
            throw new Exception('curr lesson has unsolved exams', 403);
        }

        $nextLessonId = Lesson::getNextLessonId($lesson, false);

        if (!$nextLessonId or $nextLessonId <= 0) {
            return $nextLessonId;
        }

        $this->store($nextLessonId);

        return $nextLessonId;
    }
}
