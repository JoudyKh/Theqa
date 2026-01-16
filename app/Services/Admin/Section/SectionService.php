<?php

namespace App\Services\Admin\Section;

use App\Models\Exam;
use App\Models\User;
use App\Models\Lesson;
use App\Models\Section;
use App\Models\StudentExam;
use App\Constants\Constants;
use App\Models\LessonStudent;
use App\Models\SectionStudent;
use App\Constants\Notifications;
use App\Events\CourseSubscribed;
use PhpParser\Node\Stmt\ElseIf_;
use App\Models\CertificateRequest;
use Illuminate\Support\Facades\DB;
use App\Models\SubscriptionRequest;
use App\Events\CourseSubscribedEvent;
use App\Http\Resources\LessonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use App\Http\Resources\StudentExamResource;
use App\Http\Resources\Section\CourseResource;
use App\Http\Resources\Section\SectionResource;
use App\Notifications\CourseSubsribedNotification;
use App\Services\App\StudentExam\StudentExamService;
use App\Services\App\LessonStudent\LessonStudentService;
use App\Services\General\Notification\NotificationService;
use App\Http\Requests\Api\Admin\Section\OpenSectionRequest;
use App\Http\Requests\Api\Admin\Section\StoreSectionRequest;
use App\Http\Requests\Api\Admin\Section\CancelSectionRequest;
use App\Http\Requests\Api\Admin\Section\UpdateSectionRequest;
use App\Http\Requests\Api\General\Section\GetAllSectionRequest;

class SectionService
{
    public function __construct(
        protected LessonStudentService $lessonStudentService,
        protected StudentExamService $studentExamService,
        protected NotificationService $notificationService
    ) {
    }
    public function cancel(CancelSectionRequest &$request)
    {
        return DB::transaction(function () use (&$request) {

            $filesToDelete = [];

            SectionStudent::where([
                'section_id' => $request->validated('section_id'),
                'student_id' => $request->validated('student_id'),
            ])->forceDelete();

            $section = Section::where('id', $request->validated('section_id'))->first();

            if ($section) {
                $childSectionIdsArray = $section->subSections()?->pluck('id')?->toArray();

                if ($childSectionIdsArray) {
                    $studentSection = SectionStudent::where([
                        'student_id' => $request->validated('student_id'),
                    ])
                        ->whereIn('section_id', $childSectionIdsArray)
                        ->forceDelete();
                }
            }

            $studentExamIds = DB::table('exams')->where([
                'model_type' => Section::class,
                'model_id' => $request->validated('section_id'),
            ])
                ->join('student_exams', 'student_exams.exam_id', '=', 'exams.id')
                ->where('student_exams.student_id', $request->validated('student_id'))
                ->pluck('student_exams.id')
                ->toArray();

            //this will cascade the student_answers
            StudentExam::whereIn('id', $studentExamIds)->forceDelete();



            DB::afterCommit(function () use (&$filesToDelete) {
                Storage::disk('public')->delete($filesToDelete);
            });

            return success();
        });
    }
    public function open(OpenSectionRequest &$request)
    {
        return DB::transaction(function () use (&$request) {

            $sectionId = $request->validated('section_id');
            $studentId = $request->validated('student_id');

            $studentSection = SectionStudent::create([
                'section_id' => $sectionId,
                'student_id' => $studentId,
            ]);



            $user = User::findOrFail($studentId);

            DB::afterCommit(function () use ($user) {

                $data = [
                    'clickable' => false,
                    'params' => [],
                    'state' => CourseSubsribedNotification::STATE,
                ];
                event(new CourseSubscribedEvent($user, $data));

            });

            return success();
        });
    }
    public function storeTransaction(StoreSectionRequest &$request, string $type, Section &$parentSection = null): Section
    {
        return DB::transaction(function () use (&$request, $type, $parentSection) {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->storePublicly('sections/images', 'public');
            }
            if ($request->hasFile('intro_video')) {
                $data['intro_video'] = $request->file('intro_video')->storePublicly('sections/intro-videos', 'public');
            }

            if ($parentSection) {
                $data['parent_id'] = $parentSection->id;
                if (!in_array($type, Constants::CHILDREN_OF[$parentSection->type] ?? [])) {
                    throw new \Exception('section type error', 422);
                }
            } else {
                if (!in_array($type, Constants::PARENTS)) {
                    throw new \Exception('section type error', 422);
                }
            }

            $data['type'] = $type;

            $section = Section::create($data);

            return $section;
        });
    }

    public function updateTransaction(UpdateSectionRequest &$request, Section &$section): ?bool
    {
        $data = $request->except(['image', 'intro_video']);

        try {
            return DB::transaction(function () use (&$request, &$section, &$data) {

                if ($request->has('image')) {
                    $data['old_image'] = $section->image;
                    $data['image'] = null;
                    if ($request->hasFile('image')) {
                        $data['image'] = $request->file('image')->storePublicly('sections/images', 'public');
                    }
                }

                if ($request->has('intro_video')) {
                    $data['old_intro_video'] = $section->intro_video;
                    $data['intro_video'] = null;
                    if ($request->hasFile('intro_video')) {
                        $data['intro_video'] = $request->file('intro_video')->storePublicly('sections/intro-videos', 'public');
                    }
                }

                DB::afterCommit(function () use (&$data) {
                    if (isset($data['old_image']) and Storage::exists($data['old_image'])) {
                        Storage::delete($data['old_image']);
                    }
                    if (isset($data['old_intro_video']) and Storage::exists($data['old_intro_video'])) {
                        Storage::delete($data['old_intro_video']);
                    }
                });

                $section->update($data);
            });
        } catch (\Throwable $th) {
            if (isset($data['image']) and is_string($data['image']) and Storage::exists($data['image'])) {
                Storage::delete($data['image']);
            }
            if (isset($data['intro_video']) and is_string($data['intro_video']) and Storage::exists($data['intro_video'])) {
                Storage::delete($data['intro_video']);
            }
            throw $th;
        }

    }
}
