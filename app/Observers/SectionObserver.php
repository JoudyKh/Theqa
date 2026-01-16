<?php

namespace App\Observers;

use App\Models\Exam;
use App\Models\Lesson;
use App\Models\Section;
use App\Models\StudentExam;
use App\Constants\Constants;
use App\Models\SectionStudent;

class SectionObserver
{
    public $afterCommit = true;
    public function created(Section $section)
    {
        if ($section->type == Constants::SECTION_TYPE_COURSE_SECTIONS) {
            //todo move to a job
            $prevSection = Section::where('parent_id', $section->parent_id)
                ->whereNot('id', $section->id)
                ->orderByDesc('created_at')
                ->first();

            if (!$prevSection) {
                $subscribedStudentIds = SectionStudent::where('section_id', $section->parent_id)
                    ->pluck('student_id')->toArray();

                if (!empty($subscribedStudentIds)) {
                    $section->students()->attach($subscribedStudentIds, [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

            }
        }
    }

    public function deleting(Section $section)
    {
        $examsIds = null;

        $examsIds = $section->exams()->pluck('exams.id')->toArray();

        if ($examsIds and !empty($examsIds)) {

            StudentExam::whereIn('exam_id', $examsIds)->delete();

            $section->exams()->update([
                'model_id' => null,
                'model_type' => null,
            ]);
        }




        foreach ($section->subSections as $subSection) {
            $subSection->delete();
        }
    }
}
