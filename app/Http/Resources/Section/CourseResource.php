<?php

namespace App\Http\Resources\Section;

use App\Models\Lesson;
use App\Models\Section;
use App\Constants\Constants;
use Illuminate\Http\Request;
use App\Models\SectionStudent;
use App\Http\Resources\BookResource;
use App\Http\Resources\ExamResource;
use App\Http\Resources\LessonResource;
use App\Http\Resources\TeacherResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Pagination\AbstractPaginator
     */
    public static function collection($data)
    {
        /*
        This simply checks if the given data is and instance of Laravel's paginator classes
         and if it is,
        it just modifies the underlying collection and returns the same paginator instance
        */
        if (is_a($data, \Illuminate\Pagination\AbstractPaginator::class)) {
            $data->setCollection(
                $data->getCollection()->map(function ($listing) {
                    return new static($listing);
                })
            );
            return $data;
        }

        return parent::collection($data);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        // $attributes = array_keys(config('lms_systems')[strtolower(config('app.name'))]['features']['sections']['layers'][$this->type]['attributes']) ;

        // $data = array_intersect_key($data , array_flip($attributes)) ;

        $data['teachers'] = $this->whenLoaded('teachers', fn() => TeacherResource::collection($this->teachers), null);

        $data = array_merge($data, [
            'intro_video' => $this->intro_video,
            'sub_sections' => $this->whenLoaded('subSections', fn() => SectionResource::collection($this->subSections), null),
            'lessons' => $this->whenLoaded('lessons', fn() => LessonResource::collection($this->lessons), null),
            'exams' => $this->whenLoaded('exams', fn() => ExamResource::collection($this->exams), null),
            'subscribed' => $this->subscribed,
            'lessons_count' => $this->lessons_count,
            'total_lessons_time' => $this->total_lessons_time,


            'parent_section' => $this->whenLoaded('parentSection', function () {
                $parentSection = $this->parentSection;

                if ($parentSection and $parentSection?->id) {
                    if ($parentSection->type == Constants::SECTION_TYPE_COURSES) {
                        return CourseResource::make($parentSection);
                    } else {
                        return SectionResource::make($parentSection);
                    }
                }

                return [];

            }, null),
            'created_at' => $this->created_at,
        ]);



        return $data;
    }
}
