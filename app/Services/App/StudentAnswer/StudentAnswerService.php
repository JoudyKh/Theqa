<?php

namespace App\Services\App\StudentAnswer;
use App\Models\StudentExam;
use App\Models\StudentAnswer;

class StudentAnswerService
{
    public function BulkUpsert(StudentExam &$studentExam , array $data)
    {
        $answer = [] ;

        foreach($data['answers'] as $answerData)
        {
            $answer[] = [
                'student_exam_id' => $studentExam->id ,
                'question_id' => $answerData['question_id'] ,
                'option_id' => $answerData['option_id'] ,
                'created_at' => now() ,
                'updated_at' => now() ,
            ] ;
        }

        return StudentAnswer::upsert($answer , 
        ['student_exam_id' , 'question_id'] ,
        ['option_id' , 'updated_at']) ;
    }
}
