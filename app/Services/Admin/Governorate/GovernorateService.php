<?php

namespace App\Services\Admin\Governorate;
use DB;
use App\Models\Governorate;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\GovernorateResource;

class GovernorateService
{
    public function __construct()
    {
    }
    public function getAll($trashOnly)
    {
        $governorates = Governorate::with([
            'cities' => function($query){
                $query->withCount('students') ;
            } 
        ])
        ->withCount('students')
        ->orderByDesc($trashOnly ? 'deleted_at' : 'created_at');

        if ($trashOnly) {
            $governorates->onlyTrashed();
        }
        
        $governorates =
            request()->boolean('get') ?
            $governorates->get() :
            $governorates->paginate(config('app.pagination_limit'));
            
        return success(GovernorateResource::collection($governorates));
    }

    public function store(array $data): Governorate
    {
        if (request()->hasFile('image')) {
            $data['image'] = request()->file('image')->storePublicly('governorates', 'public');
        }
        return Governorate::create($data);
    }
    public function update(Governorate &$governorate, array $data): bool
    {
        if (request()->has('image') and $governorate->image and Storage::disk('public')->exists($governorate->image)) {
            Storage::disk('public')->delete($governorate->image);
        }
        if (request()->hasFile('image')) {
            $data['image'] = request()->file('image')->storePublicly('governorates', 'public');
        }
        $governorate->update($data);

        return true;
    }
    public function delete(Governorate $governorate, $force)
    {
        if($governorate->students()->exists()){
            return error(__('messages.governorate_has_students'));
        }
        
        DB::transaction(function()use(&$governorate , $force){
            if ($force) {
                $governorate->cities()->forceDelete();
                return $governorate->forceDelete();
            }else{
                $governorate->cities()->delete();
                return $governorate->deleteOrFail();
            }
        }) ;

        return success() ;
    }
}