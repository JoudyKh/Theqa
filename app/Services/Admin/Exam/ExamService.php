<?php

namespace App\Services\Admin\Exam;

use App\Http\Resources\QuestionResource;
use Exception;
use Carbon\Carbon;
use App\Models\Exam;
use App\Models\User;
use App\Models\Lesson;
use App\Models\Section;
use App\Models\Question;
use App\Models\StudentExam;
use App\Constants\Constants;
use App\Models\SectionStudent;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ExamResource;
use App\Http\Resources\StudentResource;
use Illuminate\Support\Facades\Storage;
use App\Services\General\Lesson\LessonService;
use App\Http\Resources\Section\SectionResource;
use App\Services\Admin\Question\QuestionService;
use App\Services\App\StudentExam\StudentExamService;
use App\Http\Requests\Api\Admin\Exam\CloneExamRequest;
use App\Http\Requests\Api\Admin\Exam\StoreExamRequest;
use App\Http\Requests\Api\Admin\Exam\UpdateExamRequest;
use App\Http\Requests\Api\Admin\Exam\GetAllExamsRequest;
use App\Http\Requests\Api\Admin\Exam\BulkDeleteExamRequest;
use Illuminate\Support\Facades\Log;

class ExamService
{
    public function __construct(
        protected QuestionService $questionService,
        protected LessonService $lessonService,
        protected StudentExamService $studentExamService
    ) {
    }
    public function bulkDelete(BulkDeleteExamRequest &$request)
    {
        DB::transaction(function () use (&$request) {
            StudentExam::whereIn('exam_id', $request->validated('trash_exams'))->delete();
            Exam::whereIn('id', $request->validated('trash_exams'))->delete();
        });
    }

    public function clone(CloneExamRequest &$request)
    {
        return DB::transaction(function () use ($request) {
            $cloneExam = Exam::where('id', $request->validated('clone_exam_id'))
                ->with('questions.options')
                ->firstOrFail();

            $newQuestions = $cloneExam->questions->pluck('id')->toArray();

            $targetExam = Exam::where('id', $request->validated('target_exam_id'))->first();

            if (is_null($targetExam)) {
                // Replicate the exam
                $targetExam = $cloneExam->replicate();
                $targetExam->model_type = null;
                $targetExam->model_id = null;
                $targetExam->exam_order = null;

                $newName = $this->generateUniqueName($cloneExam->name);
                $targetExam->name = $newName;

                $targetExam->created_at = now();
                $targetExam->updated_at = now();

                $targetExam->save();
            } else {
                //the exam has already questions , we need to just add the different ones

                //get questions from clone that does not exits in the target
                $newQuestions = array_diff($newQuestions, $targetExam->questions->pluck('id')->toArray());
            }

            $targetExam->questions()->attach(array_unique($newQuestions));
            $targetExam->load(['questions.options']);

            return success(ExamResource::make($targetExam));
        });
    }
    private function generateUniqueName($baseName)
    {
        // Remove any old numbering in parentheses from the base name
        $cleanBaseName = preg_replace('/\s*\(\d+\)$/', '', $baseName);

        // Check for existing names starting with the clean base name
        $existingNames = Exam::where('name', 'like', "$cleanBaseName%")
            ->pluck('name')
            ->toArray();

        // If no similar names exist, return the clean base name
        if (empty($existingNames)) {
            return $cleanBaseName;
        }

        // Find the highest existing number in parentheses
        $maxNumber = 0;
        foreach ($existingNames as $name) {
            if (preg_match('/\((\d+)\)$/', $name, $matches)) {
                $maxNumber = max($maxNumber, (int) $matches[1]);
            }
        }

        // Append the next available number to the base name
        return $cleanBaseName . ' (' . ($maxNumber + 1) . ')';
    }

    public function search()
    {
        $exams = Exam::search(request()->input('search', ''));

        return ExamResource::collection($exams);
    }

    public function getQuestions($pageNumber, $paginate, $subjectId, $onlyPages = false)
    {
        $questions = Question::query();
        app()->instance('is_admin', auth('sanctum')->check() and auth('sanctum')->user()?->hasRole(Constants::ADMIN_ROLE));
        if ($subjectId) {
            $subject = Section::where('id', $subjectId)
                ->with('units.lessons')
                ->firstOrFail();

            $lessonsIds = $subject->units
                ->flatMap(function ($unit) {
                    return $unit->lessons->pluck('id');
                })
                ->unique()
                ->values();

            $questions->whereHas('exams', function ($q) use ($lessonsIds) {
                $q
                    ->where('model_type', Lesson::class)
                    ->whereHas('model', function ($query) use ($lessonsIds) {
                        $query->whereIn('id', $lessonsIds);
                    });
            })
                ->orderByRaw('page_number IS NULL')
                ->orderBy('page_number');
        }

        if ($onlyPages) {
            return $questions->distinct('page_number')->pluck('page_number');
        }
        if ($pageNumber) {
            $questions->where('page_number', $pageNumber);
        }
        if ($paginate) {
            $questions = $questions->paginate(25);
        } else {
            $questions = $questions->get();
        }
        return QuestionResource::collection($questions);
    }

    public function getAll(GetAllExamsRequest &$request, $authStudentId = null)
    {
        $currUser = auth('sanctum')->user();

        $extraData = [];

        if ($request->has('search')) {
            return $this->search();
        }

        if ($request->has('statistics')) {
            return $this->studentExamService->statistics($authStudentId);
        }
        $onlyGeneratedExams = $currUser && $currUser->hasRole(Constants::STUDENT_ROLE) && $request->boolean('generated_exams');

        $exams = Exam::withCount('questions')
            ->when($request->has('section_id'), function ($query) use (&$request) {
                $query->orderBy('exam_order');
            })
            ->when($request->has('student_id'), function ($query) use ($authStudentId, &$request, &$extraData) {

                $user = User::where('id', $request->get('student_id'))->firstOrFail();

                $extraData['student'] = StudentResource::make($user);

                $query->joinLastStudentExams($request->get('student_id'), null, true)
                    ->with([
                        'studentExams' => function ($q) use (&$request) {
                            $q->where('student_exams.student_id', $request->get('student_id'));
                        }
                    ]);
            })->when($authStudentId !== null, function ($query) use ($authStudentId, $onlyGeneratedExams) {
                $query->with([
                    'studentExams' => function ($q) use ($authStudentId) {
                        $q->where('student_id', $authStudentId);
                    }
                ])
                    //order by created at
                    ->joinLastStudentExams($authStudentId, null, true, $onlyGeneratedExams);
            });

        if ($request->has('exam_search')) {
            $exams->where('description', 'LIKE', '%' . trim(request()->query('exam_search', '')) . '%');
        }

        if ($request->has('without_model')) {
            if ($request->boolean('without_model')) {
                $exams->whereNull(['model_id', 'model_type']);
            } else {
                $exams->whereNotNull(['model_id', 'model_type']);
            }
        }

        if ($request->has('section_id')) {
            $section = Section::where('id', $request->get('section_id', $request->get('parent_section_id')));

            $section = $section->firstOrFail();

            $exams->where([
                'model_id' => $request->get('section_id'),
                'model_type' => Section::class,
            ]);



            if (!request()->is('*admin*') and Section::isSubscribed($section?->id, $currUser?->id)) {

                //todo make it one name
                app()->instance('subscribed_array', [$section->id]);
                app()->instance('student_courses_ids', [$section->id]);

                app()->instance(
                    'first_exam_id',
                    Section::getFirstExam($section, 0)?->id,
                );
            }

            $extraData['parent_section'] = SectionResource::make($section);

            if (request()->is('*admin*')) {
                $extra_exams = Exam::whereNull([
                    'model_id',
                    'model_type',
                ])
                    ->select(['id', 'exam_order', 'model_id', 'model_type', 'name'])
                    ->get();

                $extraData['extra_exams'] = $extra_exams;
            }
        }

        $exams->orderByDesc('exams.created_at');


        if ($onlyGeneratedExams) {
            $exams->with('questions')->where('exams.student_id', $currUser->id);
        }


        $currUser = User::with(['roles', 'studentExams'])->where('id', $authStudentId ?? auth('sanctum')->id())->first();
        if ($currUser and $currUser->hasRole(Constants::STUDENT_ROLE)) {
            Exam::getExamsState($currUser);
        }

        if ($request->has('home_limit')) {
            return $exams->limit($request->input('home_limit'))->get();
        }

        $exams = (request()->boolean('get') or request()->get('paginate') === 0) ?
            $exams->get() :
            $exams->paginate(config('app.pagination_limit'));
        return success(ExamResource::collection($exams), 200, $extraData);
    }

    public function getExamStudents(Exam $exam)
    {
        $paginatedResults = StudentExam::where('exam_id', $exam->id)
            ->whereNotNull('student_exams.total_degree')
            ->whereHas('student')
            ->with([
                'student' => function($query){
                        $query->whereNull('users.deleted_at') ;
                    }
                ])
            ->when(!request()->boolean('all_attempts'), function ($query) {
                $query->whereRaw("student_exams.start_date = (select MIN(se.start_date) from student_exams as se where se.exam_id = student_exams.exam_id AND se.student_id = student_exams.student_id )");
            })
            ->orderByDesc('start_date')
            ->paginate(config('app.pagination_limit'));

        $transformedResults = $paginatedResults->getCollection()->map(function ($result) {
            $percentage = (($result->total_degree > 0) ? ($result->degree / $result->total_degree) * 100 : 0);
            $studentName = trim(trim($result->student->full_name ?? '') ?: "{$result->student->first_name} {$result->student->last_name}");
            return [
                'student_id' => $result->student_id,
                'student_name' => $studentName,
                'marks_as_percentage' => round($percentage, 2),
                'student_marks' => round($result->exam_degree * ($percentage / 100) , 2),
                'total_marks' => round($result->exam_degree , 2),
            ];
        });

        $paginatedResults->setCollection($transformedResults);

        return $paginatedResults;
    }


    public function getExam(Exam &$exam)
    {

        $showFailedAttempts = true;
        $showAllAttempts = request()->is('*admin*');
        $showLastStudentExam = !request()->is('*admin*');

        $extraData = [];

        if (request()->has('student_id')) {
            $user = User::findOrFail(request()->get('student_id'));
        } else {
            $user = auth('sanctum')->user();
        }

        $questionQuery = $exam->questions();


        if ($exam->model_type == Section::class) {
            $parentSection = Section::with('parentSection.parentSection')->where('id', $exam->model_id)->first();

            $extraData['parent_section'] = SectionResource::make($parentSection);


        }

        if ($user != null and $user->hasRole(Constants::STUDENT_ROLE)) {

            if ($showAllAttempts) {
                $exam->load([
                    'studentExams' => function ($query) use (&$user) {
                        $query->where('student_id', $user->id);
                    }
                ]);
            }

            $exam->loadCount([
                'studentExams' => function ($query) use (&$user) {
                    $query->where('student_id', $user->id);
                }
            ]);

            $studentExam = StudentExam::with(
                ['student_answers', 'exam']
            )->where([
                        'student_id' => $user->id,
                        'exam_id' => $exam->id,
                    ])
                ->orderBy('created_at', $showLastStudentExam ? 'desc' : 'asc')
                ->first();

            if ($studentExam and (StudentExam::isPassedTheExam($studentExam) or ($studentExam->end_date and $showFailedAttempts))) {

                $resultDto = $this->lessonService->getResultDtoForStudent($user, $exam, $showLastStudentExam);

                if ($resultDto and !app()->bound('examResultDto'))
                    app()->instance('examResultDto', $resultDto->toArray());

                $nextExamId = Exam::getNextExamId($exam, false, request()->boolean('order_by_auth') ? $user : null);

                if (
                    $nextExamId == -1 or StudentExam::where([
                        'exam_id' => $nextExamId,
                        'student_id' => $user->id,
                    ])->exists()
                ) {
                    $exam->next_exam_id = $nextExamId;
                }

            } elseif ($studentExam) {
                $exam->is_open = true;
                if (!$studentExam?->end_date and $studentExam?->start_date) {
                    $exam->is_solving = true;
                    $exam->start_date = $studentExam->start_date;
                    $exam->curr_date = now()->toDateTimeString();
                    $exam->remaining_time = Carbon::now()
                        ->diffInSeconds(Carbon::parse($studentExam->start_date)->addMinutes($exam->minutes));
                }
            }


            if ($studentExam) {
                $exam->is_subscribed = true;
            } elseif ($exam->model_type == Section::class) {
                $exam->is_subscribed = Section::isSubscribed($exam->model_id);
            }
        }

        if (!request()->is('*admin*')) {
            //guest or not subscribed
            $exam->loadMissing([
                'questions' => function ($query) use (&$exam) {
                    $query->inRandomOrder()
                        ->take($exam->random_questions_max ?? $exam::RANDOM_QUESTIONS_MAX)
                        ->with(['options']);
                }
            ]);

        } else {
            $exam->paginatedQuestions = $exam->questions()
                ->orderBy('created_at')
                ->paginate(config('app.pagination_limit'));
        }

        if (request()->is('*admin*') and $exam->model_id and $exam->model_type) {
            $exams = Exam::where([
                'model_id' => $exam->model_id,
                'model_type' => $exam->model_type,
            ])
                ->where('id', '<>', $exam->id)
                ->select(['id', 'exam_order', 'model_id', 'model_type', 'name'])
                ->get();

            $extraData['extra_exams'] = $exams;
        }

        if (request()->is('*admin*')) {
            Question::chosenQuestions(null, $exam?->id ?? null, $questionQuery?->pluck('questions.id')?->toArray() ?? null, true);
        }

        return success(ExamResource::make($exam), 200, $extraData);
    }
    public function generateQuestions(array $data)
    {

        $user = $data['user'] ?? null;
        $isStudent = $data['is_student'] ?? null;
        if ($data['subjects_ids'] ?? false) {
            $subjects = Section::whereIn('id', values: $data['subjects_ids'])
                ->with(['units.lessons.exam.questions'])
                ->get();
            //the student must have access to the subject to generate questions
            if ($isStudent) {
                $studentSubjects = $user->sections()
                    ->whereIn('sections.id', $subjects->pluck('id'))
                    ->pluck('sections.id')
                    ->toArray();
                if (count($subjects) !== count($studentSubjects)) {
                    throw new Exception('you are not subscribed to some of the subjects', 422);
                }
            }

            $questions = collect();
            foreach ($subjects as $subject) {
                foreach ($subject->units as $unit) {
                    foreach ($unit->lessons as $lesson) {
                        $questions = $questions->merge($lesson->exam->questions);
                    }
                }
            }
        } elseif ($data['units_ids'] ?? false) {
            $units = Section::whereIn('id', $data['units_ids'])
                ->with(['lessons.exam.questions', 'parentSection'])
                ->get();
            if ($isStudent) {
                $subjects = $units->pluck('parentSection.id')->filter();
                $studentSubjects = $user->sections()
                    ->whereIn('sections.id', $subjects->pluck('id'))
                    ->pluck('sections.id')
                    ->toArray();
                if (count($subjects) !== count($studentSubjects)) {
                    throw new Exception('you are not subscribed to some of the units subjects ', 422);
                }
            }
            $questions = collect();
            foreach ($units as $unit) {
                foreach ($unit->lessons as $lesson) {
                    $questions = $questions->merge($lesson->exam->questions);
                }
            }
        } elseif ($data['lessons_ids'] ?? false) {
            $lessons = Lesson::whereIn('id', $data['lessons_ids'])
                ->with(['exam.questions', 'section.parentSection'])
                ->get();

            if ($isStudent) {
                $subjects = $lessons->pluck('section.parentSection.id')->filter();
                $studentSubjects = $user->sections()
                    ->whereIn('sections.id', $subjects->pluck('id'))
                    ->pluck('sections.id')
                    ->toArray();
                if (count($subjects) !== count($studentSubjects)) {
                    throw new Exception('you are not subscribed to some of the lessons subjects ', 422);
                }
            }
            $questions = collect();
            foreach ($lessons as $lesson) {
                $questions = $questions->merge($lesson->exam->questions);
            }


        }
        if (isset($questions)) {
            $uniqueQuestions = $questions->unique('id')->shuffle();
            $questionCount = $data['questions_count'];
            return $uniqueQuestions->take($questionCount)
                ->pluck('id')
                ->toArray();
        }
        return [];
    }
    public function store(array $data)
    {
        $files = [
            'new_files' => [],
            'old_files' => [],
        ];

        try {
            if ($data['auto_generate_questions'] ?? false) {
                $data['type'] = Exam::types()::GENERATED;
                $questionsIds = $this->generateQuestions($data);
                if (count($questionsIds) > 0) {
                    $data['existing_questions'] = $questionsIds;
                } else {
                    throw new Exception(__('messages.no_questions_found'), 422);
                }
            }

            if (isset($data['section_id'])) {
                $data['model_id'] = $data['section_id'];
                $data['model_type'] = Section::class;
            } elseif (isset($data['lesson_id'])) {

                Exam::where([
                    'model_id' => $data['lesson_id'],
                    'model_type' => Lesson::class,
                ])->update([
                            'model_id' => null,
                            'model_type' => null,
                        ]);

                $data['model_id'] = $data['lesson_id'];
                $data['model_type'] = Lesson::class;
            }

            if (isset($data['image'])) {
                $data['image'] = Storage::disk('public')->putFile('exams/images', $data['image']);
                array_push($files['new_files'], $data['image']);
            }

            if (isset($data['solution_file'])) {
                $data['solution_file'] = Storage::disk('public')->putFile('exams/files', $data['solution_file']);
                array_push($files['new_files'], $data['solution_file']);
            }

            if (isset($data['model_type']) and isset($data['model_id']) and $data['model_type'] == Section::class) {
                $data['exam_order'] = Exam::when()->where([
                    'model_id' => $data['model_id'],
                    'model_type' => $data['model_type'],
                ])->max('exam_order') + 1;
            }

            $exam = Exam::create($data);
            if ($data['existing_questions'] ?? false)
                $exam->questions()->attach($data['existing_questions']);
            if ($data['questions'] ?? false) {
                $newQuestions = $this->questionService->bulkInsertGetIds($data['questions']);
                $exam->questions()->attach($newQuestions['questions_ids']);

                $files['old_files'] = array_merge($files['old_files'], $newQuestions['old_files']);
                $files['new_files'] = array_merge($files['new_files'], $newQuestions['new_files']);
            }

            return array_merge($files, ['exam' => $exam]);

        } catch (\Throwable $th) {
            foreach ($files['new_files'] as $new_file) {
                if ($new_file and Storage::disk('public')->exists($new_file)) {
                    Storage::disk('public')->delete($new_file);
                }
            }
            throw $th;
        }
    }

    public function storeTransaction(StoreExamRequest &$request)
    {
        try {

            return DB::transaction(function () use (&$request) {
                $user = auth('sanctum')->user();
                $validated = $request->validated();
                if ($user && $user->hasRole(Constants::STUDENT_ROLE)) {
                    //this is for student to generate exam to him self .
                    $validated['student_id'] = $user->id;
                    $validated['auto_generate_questions'] = true;
                    $validated['user'] = $user;
                    $validated['is_student'] = true;
                    unset($validated['questions']);
                }

                $json = $this->store($validated);
                DB::afterCommit(function () use (&$json) {
                    foreach ($json['old_files'] as $old_file) {
                        if ($old_file and Storage::disk('public')->exists($old_file)) {
                            Storage::disk('public')->delete($old_file);
                        }
                    }
                });

                return $json['exam'];
            });
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function update(Exam &$exam, UpdateExamRequest &$request)
    {
        $files = [
            'new_files' => [],
            'old_files' => [],
        ];

        try {
            $data = $request->validated();

            if ($request->hasAny(['section_id', 'lesson_id'])) {

                if (isset($data['lesson_id'])) {
                    $data['model_type'] = Lesson::class;
                    $data['model_id'] = $data['lesson_id'];
                } elseif (isset($data['section_id'])) {

                    $data['model_type'] = Section::class;
                    $data['model_id'] = $data['section_id'];

                    $data['exam_order'] = Exam::when()->where([
                        'model_id' => $data['model_id'],
                        'model_type' => $data['model_type'],
                    ])->max('exam_order') + 1;

                }
            }

            if ($request->has('exam_order_replacement_id')) {
                $replacementExam = Exam::where('id', $request->validated('exam_order_replacement_id'))->firstOrFail();

                $data['exam_order'] = $replacementExam->exam_order;

                $replacementExam->update(['exam_order' => $exam->exam_order]);

            }

            if ($request->has('image')) {

                array_push($files['old_files'], $exam->image);

                if ($request->hasFile('image')) {
                    $data['image'] = $request->file('image')->storePublicly('exams/images', 'public');
                    array_push($files['new_files'], $data['image']);
                }
            }

            if ($request->has('solution_file')) {

                array_push($files['old_files'], $exam->solution_file);

                if ($request->hasFile('solution_file')) {
                    $data['solution_file'] = $request->file('solution_file')->storePublicly('exams/files', 'public');
                    array_push($files['new_files'], $data['solution_file']);
                }
            }

            if ($data['trash_questions'] ?? false)
                $exam->questions()->detach($data['trash_questions']);
            if ($data['existing_questions'] ?? false)
                $exam->questions()->detach($data['existing_questions']);
            if ($data['questions'] ?? false) {
                $newQuestions = $this->questionService->bulkInsertGetIds($data['questions']);
                $exam->questions()->attach($newQuestions['questions_ids']);

                $files['old_files'] = array_merge($files['old_files'], $newQuestions['old_files']);
                $files['new_files'] = array_merge($files['new_files'], $newQuestions['new_files']);
            }

            DB::afterCommit(function () use (&$data, &$files) {
                if (isset($files['old_files'])) {
                    foreach ($files['old_files'] as $old_file) {
                        if ($old_file and Storage::disk('public')->exists($old_file)) {
                            Storage::disk('public')->delete($old_file);
                        }
                    }
                }
            });


            $exam->update($data);
            //observer will send notification to students when updating model id

            return array_merge($files, ['exam' => $exam]);

        } catch (\Throwable $th) {
            if (isset($files['new_files'])) {
                foreach ($files['new_files'] as $new_file) {
                    if ($new_file and Storage::disk('public')->exists($new_file)) {
                        Storage::disk('public')->delete($new_file);
                    }
                }
            }
            throw $th;
        }
    }

    public function updateTransaction(Exam &$exam, UpdateExamRequest &$request)
    {
        return DB::transaction(function () use (&$exam, &$request) {
            $json = $this->update($exam, $request);

            DB::afterCommit(function () use (&$json) {
                if (isset($json['old_files'])) {
                    foreach ($json['old_files'] as $old_file) {
                        if ($old_file and Storage::disk('public')->exists($old_file)) {
                            Storage::disk('public')->delete($old_file);
                        }
                    }
                }
            });

            return $json['exam'];
        });
    }
    public function delete(Exam &$exam, $force = false)
    {
        return DB::transaction(function () use (&$exam, $force) {
            if ($force) {
                $student_exam = StudentExam::where('exam_id', $exam->id);

                if ($student_exam->whereNotNull('degree')->exists()) {
                    throw new Exception(__('messages.student_already_solved_this_exam'));
                }

                return $student_exam->forceDelete() and $exam->forceDelete();
            }
            return $exam->deleteOrFail();
        });
    }
}
