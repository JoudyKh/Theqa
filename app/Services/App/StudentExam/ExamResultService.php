<?php

namespace App\Services\App\StudentExam;
use Exception;
use App\Models\Exam;
use App\Models\Question;
use App\DTOs\ExamResultDto;
use App\Models\StudentExam;
use App\Models\StudentAnswer;
use Illuminate\Support\Carbon;

class ExamResultService
{
    public function result(?StudentExam $studentExam, array $data, Exam|string|int $exam, $ignoreTimeOut = false)
    {
        if (!($exam instanceof Exam)) {
            $exam = Exam::where('id', $exam)->firstOrFail();
        }

        // //if time is out , the code is shorter than carbon
        // if (!$ignoreTimeOut and -now()->diffInMinutes($studentExam->start_date) >= $exam->minutes) {
        //     throw new Exception(__('messages.time_is_out'), 403);
        // }

        $result = [];

        $answers = collect($data['answers'])->keyBy('question_id')->map(function ($answer) {
            return $answer['option_id'] ?? null;
        });


        $questions = null ;

        if(app()->bound('questions'))
        {
            $questions = app('questions');
        }else{ // make the condition if the lms stores questions in stuent exam or not by default
            $questions = Question::with('options')->whereIn('id' , $answers->keys()->toArray())->get() ;
        }

        $questionsMap = $questions->map(function ($question) use ($answers) {
            $questionId = $question->id;
            $options = $question->options->map(function ($option) use ($answers, $questionId) {
                return [
                    'id' => $option?->id,
                    'name' => $option?->name,
                    'is_true' => $option?->is_true,
                    'is_chosen' => (isset($answers[$questionId]) and $option and ($option?->id == $answers[$questionId])) ?? false
                ];
            })->toArray();

            $correctOptionId = $question->options->where('is_true', 1)->first()->id ?? null;
            $studentOptionId = $answers[$questionId] ?? null;

            return [
                'id' => $question->id,
                'text' => $question->text,
                'degree' => $question->degree,
                'video' => $question->video,
                'image' => $question->image,
                'note_image' => $question->note_image,
                'note' => $question->note,
                'options' => $options,
                'true_option_id' => $correctOptionId,
                'student_option_id' => $studentOptionId,
                'chosen_and_true' => $correctOptionId == $studentOptionId,
            ];
        });

        $result = $this->calculateDegrees($questionsMap, $exam , $studentExam);

        $examArray = $exam->toArray();
        $examArray['questions'] = $questionsMap;

        $resultDto = new ExamResultDto($result, $examArray, $studentExam);

        return $resultDto;
    }

    public function calculateDegrees($questionsMap, Exam &$exam , StudentExam &$student_exam = null)
    {
        $totalDegree = 0;
        $studentDegree = 0;

        foreach ($questionsMap as $question) {

            // Add the question degree to the total degree
            $totalDegree += $question['degree'];

            // Check if student's option is correct
            if ($question['student_option_id'] === $question['true_option_id']) {
                $studentDegree += $question['degree'];
            }
        }

        if(!$totalDegree)$totalDegree = $student_exam?->total_degree ?? 0 ;

        $pass = $totalDegree ? ($studentDegree / $totalDegree) >= ($student_exam->exam_pass_percentage / 100) : null;

        //it exists in the exam but to put all results in on key
        $student_percentage = $totalDegree ? ((100 * $studentDegree) / $totalDegree) : 0;

        return [
            'pass' => $pass,
            'pass_percentage' => $exam->pass_percentage,
            'student_percentage' => round($student_percentage , 2),
            'total_degree' => $totalDegree,
            'student_degree' => $studentDegree,
            'exam_degree' => $exam->degree ?? 100,
            'exam_pass_percentage' => $exam->pass_percentage ,
            'exam_student_degree' => $totalDegree ? round(($studentDegree / $totalDegree) * $exam->degree , 2) : null,
        ];
    }

    public function resultFromExam(StudentExam &$studentExam, Exam &$exam)
    {
        $data['answers'] = StudentAnswer::where([
            'student_exam_id' => $studentExam->id,
        ])->get(['question_id', 'option_id'])->toArray();

        return $this->result($studentExam, $data, $exam, true);
    }
}
