<?php

namespace App\Services\Admin\Question;

use App\Models\Exam;
use App\Models\User;
use App\Models\Option;
use App\Models\Question;
use App\Constants\Constants;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\QuestionResource;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Api\Admin\Question\StoreQuestionRequest;
use App\Http\Requests\Api\Admin\Question\UpdateQuestionRequest;

class QuestionService
{
    public function handleMedia(&$data, $oldImage = null, $oldVideo = null, $oldNoteImage = null)
    {
        $new_files = [];
        $old_files = [];

        if (array_key_exists('video', $data)) {

            //the video could be link(string)

            if ($oldVideo) {
                $old_files[] = $oldVideo;
            }

            if ($data['video'] instanceof \Illuminate\Http\UploadedFile) {
                $data['video'] = $data['video']->store('questions/videos', 'public');
                $new_files[] = $data['video'];
            }
        }

        if (array_key_exists('image', $data)) {

            if ($oldImage) {
                $old_files[] = $oldImage;
            }

            if ($data['image'] instanceof \Illuminate\Http\UploadedFile) {
                $data['image'] = $data['image']->store('questions/images', 'public');
                $new_files[] = $data['image'];
            }
        }

        if (array_key_exists('note_image', $data)) {

            if ($oldNoteImage) {
                $old_files[] = $oldNoteImage;
            }

            if ($data['note_image'] instanceof \Illuminate\Http\UploadedFile) {
                $data['note_image'] = $data['note_image']->store('questions/images', 'public');
                $new_files[] = $data['note_image'];
            }
        }

        return [
            'new_files' => $new_files ?? [],
            'old_files' => $old_files ?? [],
        ];
    }

    public function search($search = null)
    {
        $questions = Question::search($search ?? request()->query('search', ''))
            ->paginate(config('app.pagination_limit'));

        return QuestionResource::collection($questions);
    }
    public function getAll(FormRequest &$request)
    {
        if (request()->has('search')) {
            return $this->search(request()->query('search', ''));
        }

        $questions = Question::orderByDesc('created_at')
            ->when($request->query('trash', false), function ($query) {
                return $query->onlyTrashed();
            });

        if (request()->has('question_search')) {
            $questions->where('note', 'LIKE', '%' . request()->query('question_search', '') . '%')
                ->orWhere('note', 'LIKE', '%' . request()->query('question_search', '') . '%');
        }

        $questions = request()->boolean('paginate') ?
            $questions->paginate(15) :
            $questions->get();

        return QuestionResource::collection($questions);
    }
    public function bulkInsertGetIds(array $questionsData): array
    {
        $files = [
            'new_files' => [],
            'old_files' => [],
        ];

        $options = [];

        foreach ($questionsData as $question) {
            $json = self::handleMedia($question);

            $files = [
                'new_files' => array_merge($json['new_files'], $files['new_files']),
                'old_files' => array_merge($json['old_files'], $files['old_files']),
            ];

            $questionId = Question::create($question)->id;

            foreach ($question['options'] as $optionData) {
                
                $options[] = [
                    'name' => $optionData['name'],
                    'is_true' => $optionData['is_true'],
                    'question_id' => $questionId,
                ];
                
            }
        }

        Option::insert($options);

        return [
            'questions_ids' => array_unique(array_column($options, 'question_id')),
            'new_files' => $files['new_files'],
            'old_files' => $files['old_files'],
        ];
    }
    public function bulkDelete(array $questionsIds)
    {
        Option::whereIn('question_id', $questionsIds)->delete();
        Question::whereIn('id', $questionsIds)->delete();
    }
    public function store(array $data): Question
    {
        $question = Question::create($data);

        $options = [];

        foreach ($data['options'] as $option) {
            $options[] = [
                'name' => $option['name'],
                'is_true' => $option['is_true'],
                'question_id' => $question->id,
            ];
        }

        Option::insert($options);

        return $question;
    }
    public function storeTransaction(StoreQuestionRequest &$request)
    {
        $files = [];

        try {
            return DB::transaction(function () use (&$request, &$files) {
                $data = $request->validated();
                $files = self::handleMedia($data);

                DB::afterCommit(function () use (&$files) {
                    foreach ($files['old_files'] as $old_file) {
                        if ($old_file and Storage::disk('public')->exists($old_file)) {
                            Storage::disk('public')->delete($old_file);
                        }
                    }
                });

                return $this->store($data);
            });
        } catch (\Throwable $th) {
            foreach ($files['new_files'] as $new_file) {
                if ($new_file and Storage::disk('public')->exists($new_file)) {
                    Storage::disk('public')->delete($new_file);
                }
            }
            throw $th;
        }
    }
    public function update(Question &$question, array $data): ?bool
    {
        if ($data['update_options'] ?? false) {
            foreach ($data['update_options'] as $option) {
                Option::where('id', $option['id'])->update([
                    'name' => $option['name'],
                    'is_true' => $option['is_true'],
                ]);
            }   
        }
    

    if ($data['existing_options'] ?? false) {
        foreach ($data['existing_options'] as $option) {
            Option::where('id', $option['id'])->first()->update($option);
        }
    }
    if ($data['options'] ?? false) {
        $options = [];

        foreach ($data['options'] as $option) {
            $options[] = [
                'name' => $option['name'],
                'is_true' => $option['is_true'],
                'question_id' => $question->id,
            ];
            }
            Option::insert($options);
        }
        if ($data['trash_options'] ?? false) {
            //here i used force delete , soft delete the option only when deleting the question
            Option::whereIn('id', $data['trash_options'])->forceDelete();
        }
        return $question->update($data);
    }


    public function updateTransaction(Question &$question, UpdateQuestionRequest &$request)
    {
        $files = [];

        try {
            return DB::transaction(function () use (&$question, &$request, $files) {
                $data = $request->validated();
                $files = self::handleMedia($data, $question->image, $question->video, $question->note_image);

                DB::afterCommit(function () use (&$files) {
                    foreach ($files['old_files'] as $old_file) {
                        if ($old_file and Storage::disk('public')->exists($old_file)) {
                            Storage::disk('public')->delete($old_file);
                        }
                    }
                });

                return $this->update($question, $data);
            });
        } catch (\Throwable $th) {
            foreach ($files['new_files'] as $new_file) {
                if ($new_file and Storage::disk('public')->exists($new_file)) {
                    Storage::disk('public')->delete($new_file);
                }
            }
            throw $th;
        }
    }

    public function delete(Question &$question, $force = false): ?bool
    {
        if ($force) {
            return $question->forceDelete();
        }
        return $question->deleteOrFail();
    }
}
