<?php

namespace App\Services\General\Governorate;
use App\Models\Governorate;
use App\Http\Resources\GovernorateResource;

class GovernorateService
{
    public function getAll()
    {
        $governorates = Governorate::with([
            'cities' => function($query){
                $query->withCount('students') ;
            }
        ])->orderByDesc('created_at') ;
        
        $governorates = request()->boolean('get') ? $governorates->get() : $governorates->paginate(config('app.pagination_limit'));
        return success(GovernorateResource::collection($governorates));
    }
}