<?php

namespace App\Services\Admin\Lesson;

use App\Models\Exam;
use App\Models\Lesson;
use App\Models\Section;
use App\Constants\MorphConstants;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\LessonResource;
use Illuminate\Support\Facades\Storage;
use App\Services\Admin\Exam\ExamService;
use Illuminate\Foundation\Http\FormRequest;
use App\Services\General\Storage\File\FileService;
use App\Http\Requests\Api\Admin\Lesson\StoreLessonRequest;
use App\Http\Requests\Api\Admin\Lesson\UpdateLessonRequest;

class LessonService
{
    public function __construct(
        protected FileService $filesService,
        protected ExamService $examService
    ) {
    }

    public function storeTransaction($parentSection, StoreLessonRequest &$request)
    {
        return DB::transaction(function () use ($parentSection, &$request) {
            return $this->store($parentSection, $request);
        });
    }

    public function store($parentSection, StoreLessonRequest &$request)
    {
        $data = $request->validated();
        $coverImagePath = null;
        if ($request->hasFile('cover_image')) {
            $coverImagePath = $request->file('cover_image')->storePublicly('lessons/images', 'public');
            $data['cover_image'] = $coverImagePath;
        }

        if (!isset($data['section_id']))
            $data['section_id'] = $request->get('section_id');

        $data['lesson_order'] = Lesson::where('section_id', $data['section_id'])->max('lesson_order') + 1;

        try {
            $data['lesson_order'] = Lesson::where([
                'section_id' => $parentSection,
            ])->max('lesson_order') + 1;

            $lesson = Lesson::create($data);
            if (isset($data['exam_id'])) {
                Exam::findOrFail($data['exam_id'])
                    ->update([
                        'model_type' => Lesson::class,
                        'model_id' => $lesson->id,
                    ]);

                Exam::findOrFail($data['exam_id'])
                    ->update([
                        'model_type' => Lesson::class,
                        'model_id' => $lesson->id,
                    ]);
            } else if ($data['exam'] ?? false) {

                $data['exam'] = array_merge($data['exam'], [
                    'model_type' => Lesson::class,
                    'model_id' => $lesson->id,
                ]);

                $this->examService->store($data['exam']);
            }


            if ($data['files'] ?? false) {
                $this->filesService->bulkInsert($data['files'], '/lessons/files/', 'public', Lesson::class, $lesson->id);
            }

            $lesson->loadMissing(['exam.questions.options']);

            return $lesson;

        } catch (\Exception $e) {

            if ($coverImagePath and Storage::disk('public')->exists($coverImagePath)) {
                Storage::disk('public')->delete($coverImagePath);
            }

            throw $e;
        }
    }
    public function updateTransaction(Lesson &$lesson, UpdateLessonRequest &$request)
    {
        return DB::transaction(function () use (&$lesson, &$request) {
            return $this->update($lesson, $request);
        });
    }
    public function update(Lesson &$lesson, UpdateLessonRequest &$request)
    {
        $data = $request->validated();
        $newCoverImagePath = null;

        try {

            if ($request->has('lesson_order_replacement_id')) {
                $replacementLesson = Lesson::where('lesson_order', $request->validated('lesson_order_replacement_id'))->firstOrFail();

                $data['lesson_order'] = $replacementLesson->lesson_order;

                $replacementLesson->update(['lesson_order' => $lesson->lesson_order]);
            }

            if ($request->hasFile('cover_image')) {
                $newCoverImagePath = $request->file('cover_image')->storePublicly('lessons/images', 'public');
                $data['cover_image'] = $newCoverImagePath;
                if
                (Storage::disk('public')->exists($lesson->cover_image)) {
                    Storage::disk('public')->delete($lesson->cover_image);
                }
            }

            if ($data['files'] ?? false) {
                $this->filesService->bulkInsert($data['files'], '/lessons/files/', 'public', Lesson::class, $lesson->id);
            }

            if ($data['trash_files'] ?? false) {
                $this->filesService->bulkDelete($data['trash_files']);
            }

            $lesson->update($data);

            if ($request->has('exam_id') or $request->has('exam')) {
                
                Exam::where([
                    'model_id' => $lesson->id,
                    'model_type' => Lesson::class,
                ])->update([
                            'model_id' => null,
                            'model_type' => null,
                        ]);

                if ($request->get('exam_id') != null) {
                    Exam::where([
                        'id' => $request->input('exam_id'),
                    ])->update([
                                'model_id' => $lesson->id,
                                'model_type' => Lesson::class,
                            ]);

                } else if ($request->has('exam')) {
                    $examData = array_merge(
                        $request->exam,
                        [
                            'model_id' => $lesson->id,
                            'model_type' => Lesson::class,
                        ]
                    );
                    $this->examService->store($examData);
                }
            }

            $lesson->loadMissing(['exam.questions.options']);

            return true;

        } catch (\Exception $e) {

            if ($newCoverImagePath and Storage::disk('public')->exists($newCoverImagePath)) {
                Storage::disk('public')->delete($newCoverImagePath);
            }

            throw $e;
        }
    }


    public function delete(Lesson &$lesson)
    {
        // $lesson->exam()->dissociate()->save();
        Exam::where([
            'model_type' => Lesson::class,
            'model_id' => $lesson->id,
        ])->update([
                    'model_type' => null,
                    'model_id' => null,
                ]);

        if (request()->boolean('force')) {
            return $lesson->forceDelete();
        }

        return $lesson->deleteOrFail();
    }
}
