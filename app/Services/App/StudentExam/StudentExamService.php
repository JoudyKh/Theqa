<?php

namespace App\Services\App\StudentExam;

use Exception;
use App\Models\Exam;
use App\Models\User;
use App\Models\Lesson;
use App\Models\Section;
use App\Models\StudentExam;
use App\Constants\Constants;
use App\Models\LessonStudent;
use Illuminate\Support\Carbon;
use App\Jobs\WhatsAppMessageJob;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ExamResource;
use App\Http\Resources\StudentExamResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Services\App\LessonStudent\LessonStudentService;
use App\Services\App\StudentAnswer\StudentAnswerService;
use App\Services\App\SectionStudent\SectionStudentService;
use App\Http\Requests\Api\App\StudentExam\StoreStudentExamRequest;
use App\Http\Requests\Api\App\StudentExam\CreateStudentExamRequest;

class StudentExamService
{
    public function __construct(
        protected StudentAnswerService $studentAnswerService,
        protected ExamResultService $examResultService,
        protected LessonStudentService $lessonStudentService,
        protected SectionStudentService $sectionStudentService
    ) {
    }

    public static function statistics($studentId = null, $studentExamOrder = 'ASC', $allAttempts = false)
    {
        // $query = StudentExam::with(['exam'])
        //     ->whereNotNull('total_degree')
        //     ->where('student_id', $studentId ?? auth('sanctum')->id())
        //     ->whereHas('exam');

        // // If $allAttempts is false, only fetch the first attempt
        // if (!$allAttempts) {
        //     $query->whereIn('id', function ($subQuery) {
        //         $subQuery->selectRaw('MIN(id)')
        //             ->from('student_exams')
        //             ->groupBy('student_id', 'exam_id');
        //     });
        // }

        // $studentExams = $query->orderBy('id', $studentExamOrder)->get();

        // // Map statistics for the filtered exams
        // $statistics = $studentExams->map(function ($studentExam) {
        //     $exam = $studentExam->exam;

        //     $degree = $studentExam?->degree ?? 0;
        //     $total_degree = $studentExam?->total_degree ?? 0;

        //     $studentPercentage = $total_degree ? round(($degree * 100) / $total_degree, 2) : 0;

        //     return [
        //         'id' => $exam?->id,
        //         'name' => $exam?->name,
        //         'pass' => $studentPercentage >= $exam?->pass_percentage,
        //         'degree' => $degree,
        //         'total_degree' => $total_degree,
        //         'student_percentage' => intval($studentPercentage ?? 0),
        //         'start_date' => $studentExam?->start_date ?? 0,
        //         'end_date' => $studentExam?->end_date ?? 0,
        //     ];
        // });

        // $totalAvg = $statistics->isNotEmpty()
        //     ? round($statistics->pluck('student_percentage')->avg(), 2)
        //     : 0;

        // // Calculate success and failure counts
        // $success_count = $statistics->filter(function ($stat) {
        //     return $stat['pass'];
        // })->count();

        // $faileure_count = $statistics->filter(function ($stat) {
        //     return !$stat['pass'];
        // })->count();

        // // Return the response
        // return success($statistics->values(), 200, [
        //     'success_count' => $success_count,
        //     'faileure_count' => $faileure_count,
        //     'total_avg' => $totalAvg,
        // ]);
    }

    public static function openFirstExam(string|int $sectionId, string|int|null $studentId = null, $depthSectionLevel = 0)
    {
        $exams = Exam::query();
        $student = User::findOrFail($studentId ?? auth('sanctum')->id());
        $sectionId == $sectionId;


        $firstExam = $exams->where([
            'model_id' => $sectionId,
            'model_type' => Section::class,
        ])
            ->orderBy('exam_order')
            ->first();

        if (!$firstExam) {
            return error(__('messages.section_has_no_exam'), null, 422);
        }

        $studentId = $studentId ?? auth('sanctum')->id();

        $studentExam = StudentExam::where([
            'student_id' => $studentId,
            'exam_id' => $firstExam->id,
        ])->first();

        if ($studentExam) {
            return error(__('messages.student_already_opened_the_exam'));
        }

        $studentExam = StudentExam::updateOrCreate(
            [
                'student_id' => $studentId,
                'exam_id' => $firstExam->id,
            ],
            [
                'exam_degree' => $firstExam->degree ?? 100,
                'exam_pass_percentage' => $firstExam->pass_percentage,
                'attempts_count' => 0
            ]
        );
    }

    public function openNextExam(Exam &$exam)
    {
        $student = User::with(['studentExams'])->where('id', auth('sanctum')->id())
            ->whereHas('roles', function ($role) {
                $role->where('name', Constants::STUDENT_ROLE);
            })
            ->firstOrFail();

        $currStudentExam = $student->studentExams()->where('exam_id', $exam->id)
            ->orderByDesc('student_exams.created_at')
            ->first();

        if (!$currStudentExam) {
            throw new Exception('current exam is not open');
        }
        if ($exam->questions()->count() > 0 and !StudentExam::isPassedTheExam($currStudentExam)) {
            throw new Exception('current exam is not solved');
        }

        $nextExamId = Exam::getNextExamId($exam, false);

        if ($nextExamId == -1)
            return -1;

        $nextExam = Exam::findOrFail($nextExamId);

        StudentExam::firstOrCreate([
            'exam_id' => $nextExamId,
            'student_id' => $student->id,
        ], [
            'exam_degree' => $nextExam->degree ?? 100,
            'exam_pass_percentage' => $nextExam->pass_percentage
        ]);

        return $nextExamId;
    }

    public function getAll()
    {
        $exams = StudentExam::where('student_id', auth('sanctum')->id());

        return StudentExamResource::collection($exams->paginate(config('app.pagination_limit')));
    }

    public function show(StudentExam &$studentExam)
    {
        return StudentExamResource::make($studentExam->load('student_answers'));
    }

    public static function createNewStudentExam(Exam &$exam, User|Authenticatable &$user): StudentExam
    {
        return $studentExam = StudentExam::create([
            'student_id' => $user->id,
            'exam_id' => $exam->id,
            'exam_degree' => $exam->degree ?? 100,
            'exam_pass_percentage' => $exam->pass_percentage,
            'start_date' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
            'created_at' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Student whom did not pass this exam can start another attempt here if not first
     * @param \App\Http\Requests\Api\App\StudentExam\CreateStudentExamRequest $request
     */
    public function createTransaction(CreateStudentExamRequest &$request, Exam $exam)
    {
        return DB::transaction(function () use (&$request, $exam) {

            $user = auth('sanctum')->user();
            //make rule so we can easily extract them into config

            $createifNotFound = true;
            $createIfFinished = true;
            $stopAfterSuccess = false;

            $setCurrStudentExamToFailIfCreate = true;

            $createBeforeTimeIsPassed = true;
            $resetBeforeTimeIsPassed = false;
            $restrictBeforeTimeIsPassed = false;

            $createAfterTimeIsPassed = true;
            $resetAfterTimeIsPassed = false;
            $restrictAfterTimeIsPassed = false;



            //collect data

            if (!$exam->model_id) {
                return error('you cant solve exam without model', [], 422);
            }

            $isSubscribed = null;
            $currLesson = null;

            if ($exam->model_type == Section::class) {
                $isSubscribed = Section::isSubscribed($exam->model_id);
            } elseif ($exam->model_type == Lesson::class) {
                $currLesson = Lesson::findOrFail($exam->model_id);
                $isSubscribed = Section::isSubscribed($currLesson->section_id);
            }

            if (!$isSubscribed && !$currLesson?->is_free and $exam->student_id != $user->id && $exam->type !== 'GENERATED') {
                return error('user is not subscribed', [], 403);
            }

            if ($exam->type === 'GENERATED' && $exam->student_id && $exam->student_id != $user->id) {
                return error('you are not allowed to solve this exam', [], 403);
            }

            //handle case admin deleted first exam or lesson

            $studentExam = StudentExam::with(['student_answers'])->where([
                'student_id' => $user->id,
                'exam_id' => $exam->id,
            ])->orderByDesc('created_at')->first();

            if ($studentExam and $studentExam?->start_date === null) {
                $studentExam->update([
                    'exam_degree' => $exam->degree ?? 100,
                    'exam_pass_percentage' => $exam->pass_percentage,
                    'start_date' => now()->toDateTimeString(),
                    'updated_at' => now()->toDateTimeString(),
                ]);

                return StudentExamResource::make($studentExam);
            }

            if ($studentExam?->end_date and $createIfFinished) {
                $studentExam = $this->createNewStudentExam($exam, $user);
                return StudentExamResource::make($studentExam);
            }


            if (!$studentExam and $createifNotFound) {
                $studentExam = $this->createNewStudentExam($exam, $user);
                return StudentExamResource::make($studentExam);
            }

            //validate data
            if ($stopAfterSuccess and $studentExam) {
                throw_if($studentExam->degree, new Exception(__('messages.you_passed_this_exam'), 422));
            }

            if ($studentExam and $studentExam?->start_date !== null and $studentExam?->end_date === null) {
                $haveTime = Carbon::now()->diffInSeconds(Carbon::parse($studentExam->start_date)->addMinutes($exam->minutes)) > 0;

                if ($haveTime) {
                    throw_if(
                        $restrictBeforeTimeIsPassed,
                        new Exception(__('messages.you_still_solving'), 422)
                    );

                    if ($resetBeforeTimeIsPassed) {
                        $studentExam->update([
                            'start_date' => now()->toDateTimeString(),
                            'attempts_count' => ($studentExam->attempts_count ?? 0) + 1,
                            'updated_at' => now()->toDateTimeString(),
                        ]);
                        $studentExam->refresh();
                    }

                    if ($createBeforeTimeIsPassed) {
                        if ($setCurrStudentExamToFailIfCreate) {
                            $studentExam->update([
                                'end_date' => now()->toDateTimeString(),
                                'updated_at' => now()->toDateTimeString(),
                                'degree' => 0,
                                'total_degree' => $studentExam->student_answers->count() > 0 ? $studentExam->student_answers->count() : $exam->questions()->count(),
                                'on_time' => true,
                            ]);
                        }
                        $studentExam = $this->createNewStudentExam($exam, $user);
                        return StudentExamResource::make($studentExam);
                    }

                    //before time is passed
                } else {

                    throw_if(
                        $restrictAfterTimeIsPassed,
                        new Exception(__('messages.time_is_over'), 422)
                    );

                    //after time is passed
                    if ($resetAfterTimeIsPassed) {
                        $studentExam->update([
                            'start_date' => now()->toDateTimeString(),
                            'attempts_count' => ($studentExam->attempts_count ?? 0) + 1,
                            'updated_at' => now()->toDateTimeString(),
                        ]);
                        $studentExam->refresh();
                    }

                    if ($createAfterTimeIsPassed) {
                        if ($setCurrStudentExamToFailIfCreate) {
                            $studentExam->update([
                                'end_date' => now()->toDateTimeString(),
                                'updated_at' => now()->toDateTimeString(),
                                'degree' => 0,
                                'total_degree' => $studentExam->student_answers->count() > 0 ? $studentExam->student_answers->count() : $exam->questions()->count(),
                                'on_time' => false,
                            ]);
                        }
                        $studentExam = $this->createNewStudentExam($exam, $user);
                        return StudentExamResource::make($studentExam);
                    }
                }
            }






            if (!$studentExam and $exam->model_type == Lesson::class) {
                $lesson = Lesson::findOrFail($exam->model_id);

                $lessonStudent = LessonStudent::where([
                    'lesson_id' => $exam->model_id,
                    'student_id' => $user->id,
                ])->first();

                if (!$lessonStudent) {
                    $section = Section::find($lesson->section_id);
                    if (Section::getFirstLesson($section)) {
                        LessonStudent::create([
                            'lesson_id' => $exam->model_id,
                            'student_id' => $user->id,
                        ]);
                        $studentExam = $this->createNewStudentExam($exam, $user);
                        return StudentExamResource::make($studentExam);
                    } else {
                        return error('you did not open the lesson with id : ' . $exam->model_id . ' yet', [], 403);
                    }
                } else {
                    $studentExam = $this->createNewStudentExam($exam, $user);
                    return StudentExamResource::make($studentExam);
                }
            }

            if (!$studentExam) {
                return error('exam is not open yet', [], 403);
            }

            return StudentExamResource::make($studentExam);
        });
    }

    /**
     * Student will store his/her answers for a giving exam
     * @param \App\Models\StudentExam $studentExam
     * @param \App\Http\Requests\Api\App\StudentExam\StoreStudentExamRequest $request
     * @return \App\Models\StudentExam
     */
    public function storeOrUpdateAnswersTransaction(StudentExam &$studentExam, StoreStudentExamRequest &$request): StudentExam
    {
        return DB::transaction(function () use (&$studentExam, &$request) {

            $this->studentAnswerService->BulkUpsert($studentExam, $request->validated());

            return $studentExam;
        });
    }

    public function solve(Exam &$exam, StoreStudentExamRequest &$request)
    {
        $allowAfterSuccess = true;
        $showAnswersAfterFail = true;
        $end_date = Carbon::now()->toDateTimeString();

        $extraData = [];

        if ($request->student_state == 'testing') {
            return $this->test($exam, $request);
        }

        $allStudentExams = StudentExam::where([
            'exam_id' => $exam->id,
            'student_id' => auth('sanctum')->id(),
        ])->orderByDesc('created_at')->get();

        $lastStudentExam = $allStudentExams->first();

        if (!$lastStudentExam) {//must have a student exam
            throw new Exception('student exam not found', 404);
        }

        $user = auth('sanctum')->user();

        if ($lastStudentExam->start_date === null) {
            throw new Exception('create student exam first', 422);
        }

        if ($lastStudentExam->end_date != null) {//cant solve it if you did it in the past
            throw new Exception('cant add answer to an old exam , create a new exam and try again', 422);
        }

        if (!$allowAfterSuccess and $lastStudentExam->degree) {
            throw new Exception('exam already solved', 403);
        }

        $examResultDto = $this->examResultService->result($lastStudentExam, $request->validated(), $exam, false);

        $examDegree = $lastStudentExam->exam_degree;
        $examPassPercentage = $lastStudentExam->exam_pass_percentage;

        $percentage = $examResultDto->result['total_degree'] ? round((($examResultDto->result['student_degree'] ?? 0) * 100) / $examResultDto->result['total_degree'], 2) : null;
        $examStudentDegree = round(($percentage * $examDegree) / 100, 2);



        //get the json ready
        $lastStudentExam->update(array_merge([
            'degree' => $examResultDto->result['student_degree'],
            'total_degree' => $examResultDto->result['total_degree'],
            'end_date' => $end_date,
        ], [
            'on_time' => Carbon::parse($lastStudentExam->start_date)->diffInMinutes($end_date) <= $exam->minutes,
        ]));

        $lastStudentExam = $this->storeOrUpdateAnswersTransaction($lastStudentExam, $request);

        if (!$showAnswersAfterFail and !$examResultDto->result['pass']) {
            return success([
                'pass' => false,
                'message' => __('messages.you_failed'),
                'exam_degree' => $examDegree,
                'exam_pass_percentage' => $examPassPercentage,
                'exam_student_degree' => $examStudentDegree,
                'student_degree' => $examResultDto->result['student_degree'],
                'total_degree' => $examResultDto->result['total_degree'],
                'resault' => $percentage,
            ]);
        }

        // the student passed and in time so we store the answers
        $examResultDto->studentExam = $lastStudentExam;

        if (!app()->bound('examResultDto')) {
            app()->instance('examResultDto', $examResultDto->toArray());
        }

        $nextExamId = null;

        $nextExamId = Exam::getNextExamId($exam, false);
        $exam->next_exam_id = $nextExamId;

        $exam->loadCount([
            'studentExams' => function ($studentExam) use ($user) {
                $studentExam->where('student_id', $user->id);
            }
        ]);

        return success(ExamResource::make($exam), 200, $extraData ?? []);
    }

    private function generateExamMessage($percentage, $student_name, $exam_name, $student_degree, $exam_degree)
    {
        // Select the appropriate translation key based on the percentage
        if ($percentage == 100) {
            $key = 'whatsapp.percentage_100';
        } elseif ($percentage >= 75) {
            $key = 'whatsapp.percentage_75';
        } elseif ($percentage >= 40) {
            $key = 'whatsapp.percentage_40';
        } else {
            $key = 'whatsapp.percentage_below_40';
        }

        // Pass dynamic values to the translation
        return $message = __($key, [
            'full_name' => $student_name,
            'exam_name' => $exam_name,
            'student_degree' => $student_degree,
            'exam_degree' => $exam_degree,
        ]);
    }

    public function test(Exam &$exam, StoreStudentExamRequest &$request)
    {
        $examResultDto = $this->examResultService->result(null, $request->validated(), $exam, true);

        if (!$examResultDto->result['pass']) {

            $res = $examResultDto->result['total_degree'] ? round((($examResultDto->result['student_degree'] ?? 0) * 100) / $examResultDto->result['total_degree'], 2) : null;
            $examRes = $examResultDto->result['total_degree'] ? round(($examResultDto->result['student_degree'] / ($examResultDto->result['total_degree'])) * $exam->degree, 2) : null;

            return success([
                'pass' => false,
                'message' => __('messages.you_failed'),
                'exam_degree' => $exam->degree,
                'exam_pass_percentage' => $exam->pass_percentage,
                'exam_student_degree' => $examRes,
                'student_degree' => $examResultDto->result['student_degree'],
                'total_degree' => $examResultDto->result['total_degree'],
                'resault' => $res,
            ]);
        }

        if (!app()->bound('examResultDto')) {
            app()->instance('examResultDto', $examResultDto->toArray());
        }

        $exam->is_subscribed = false;

        return success(ExamResource::make($exam));
    }
}
