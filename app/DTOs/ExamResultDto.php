<?php

namespace App\DTOs;
use App\Models\Exam;
use App\Models\StudentExam;
use App\Http\Resources\ExamResource;
use App\Http\Resources\QuestionResource;

class ExamResultDto
{
    public array $result;
    public Exam|array $exam;
    public ?StudentExam $studentExam;
    public array $chosenOptionsId ;

    public function __construct($result, $exam, $studentExam)
    {
        $this->result = $result;
        $this->exam = $exam;
        $this->studentExam = $studentExam;

        $chosenOptionsId = [] ;
        foreach ($exam['questions'] as $question) {
            // Iterate over the options
            foreach ($question['options'] as $option) {
                // Check if the option is chosen
                if ($option['is_chosen'] ?? false) {
                    // Add the chosen option to the array
                    $chosenOptionsId[] = $option['id'];
                }
            }
        }
        $this->chosenOptionsId = $chosenOptionsId ;

        if ($studentExam?->relationLoaded('student_answers')) {
            $chosenOptions = $studentExam->student_answers->pluck('option_id')?->toArray() ?? [];
            $this->chosenOptionsId = array_unique(array_merge($chosenOptions ?? [] , $this->chosenOptionsId ?? [])) ; 
        } 
    }

    public function toArray():array
    {
        $data = [
            'result' =>  $this->result,
            'exam' => is_array($this->exam) ? $this->exam : $this->exam->toArray() ,
            'studentExam' => $this->studentExam?->toArray() ,
            'chosenOptionsId' => $this->chosenOptionsId ,
        ];

        return $data ;
    }
}