<?php

namespace App\Services\Admin\TopStudent;
use App\Models\TopStudent;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\TopStudentResource;

class TopStudentService
{
    public function __construct(){}
    public function getAll($trashOnly)
    {
        $top_students = TopStudent::orderByDesc($trashOnly ? 'deleted_at' : 'created_at');

        if ($trashOnly) {
            $top_students->onlyTrashed();
        }
        $top_students = $top_students->paginate(config('app.pagination_limit'));
        return TopStudentResource::collection($top_students);
    }

    public function store(array $data):TopStudent
    {
        if(request()->hasFile('image')){
            $data['image'] = request()->file('image')->storePublicly('top_student' , 'public') ;
        }
        return TopStudent::create($data) ;
    }
    public function update(TopStudent &$top_student , array $data):bool
    {
        if(request()->has('image') and $top_student->image and Storage::disk('public')->exists($top_student->image)){
            Storage::disk('public')->delete($top_student->image) ;
        }
        if(request()->hasFile('image')){
            $data['image'] = request()->file('image')->storePublicly('top_student' , 'public') ;
        }
        $top_student->update($data) ;

        return true;
    }
    public function delete(TopStudent $top_student , $force):?bool
    {
        if($force){
            return $top_student->forceDelete() ;
        }
        return $top_student->deleteOrFail() ;
    }
}
