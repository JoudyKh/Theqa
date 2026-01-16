<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Constants\Constants;
use Illuminate\Http\Request;
use App\Models\LessonStudent;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="LessonResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", description="ID of the lesson"),
 *     @OA\Property(property="name", type="string", description="Name of the lesson"),
 *     @OA\Property(property="description", type="string", description="Description of the lesson"),
 *     @OA\Property(property="video_url", type="string", description="URL of the lesson video"),
 *     @OA\Property(property="time", type="string", description="Time associated with the lesson"),
 *     @OA\Property(property="cover_image", type="string", description="URL of the cover image"),
 *     @OA\Property(
 *         property="files",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/FileResource")
 *     ),
 *     @OA\Property(
 *         property="exam",
 *         ref="#/components/schemas/ExamResource"
 *     ),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", description="Timestamp when the lesson was deleted"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the lesson was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp when the lesson was last updated")
 * )
 */
class LessonResource extends JsonResource
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
        $exam = $this->whenLoaded('exam', fn() => $this->exam, null);


        $parentSection = $request->route('parentSection');

        //you have to put parent_section_id first ,,, parentSection could be course
        $parentSectionId = ($this->parent_section_id ?? $parentSection?->id ?? $parentSection);

        $response = [
            'lesson_order' => $this->lesson_order,
            'section_id' => $this->section_id,
            'parent_section_id' => $this->parent_section_id,

            'next_lesson_id' => (app()->bound('next_lesson_id') ? app('next_lesson_id') : null),
            'next_section_id' => (app()->bound('next_section_id') ? app('next_section_id') : null),

            'id' => $this->id,
            'name' => $this->name,

            'exam_id' => $exam?->id,
            'exam' => $exam ? ExamResource::make($exam) : null,

            'is_free' => $this->is_free,
            'description' => $this->description,
            'video_url' => (app()->bound('allow_video_and_files') and app('allow_video_and_files')) ? $this->video_url : 'hidden',
            'time' => $this->time,
            'cover_image' => $this->cover_image,
            'files' => $this->whenLoaded('files', fn() => FileResource::collection($this->files), null),
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
        //i could just join it from the service
        $response['is_open'] = ($this->is_open ?? (app()->bound('first_lesson_id') ? app('first_lesson_id') : null) == $this->id);

        if (app()->bound('examResultDto')) {
            $response['is_open'] = 1;
        }

        if (!$response['is_open'] and auth('sanctum')->check()) {
            if (app()->bound('lessonStudentArray')) {
                $response['is_open'] = in_array(
                    $this->id,
                    app('lessonStudentArray')
                );
            }
        }

    
        return $response;
    }
}