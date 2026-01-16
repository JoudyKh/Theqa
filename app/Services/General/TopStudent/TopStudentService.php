<?php

namespace App\Services\General\TopStudent;
use App\Models\TopStudent;
use App\Http\Resources\TopStudentResource;

class TopStudentService
{
    public function getAll()
    {
        $top_students = TopStudent::orderByDesc('created_at')->paginate(config('app.pagination_limit'));
        return TopStudentResource::collection($top_students);
    }
}