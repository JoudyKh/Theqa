<?php

namespace App\Services\Admin\City;
use DB;
use App\Models\City;
use App\Models\Governorate;
use App\Http\Resources\CityResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\GovernorateResource;

class CityService
{
    public function __construct()
    {
    }
    public function getAll(Governorate &$governorate, $trashOnly)
    {
        $cities = City::where('governorate_id', $governorate->id)
            ->withCount('students')
            ->orderByDesc($trashOnly ? 'deleted_at' : 'created_at');

        if ($trashOnly) {
            $cities->onlyTrashed();
        }
        $cities =
            request()->boolean('get') ?
            $cities->get() :
            $cities->paginate(config('app.pagination_limit'));

        return success(CityResource::collection($cities), 200, [
            'governorate' => GovernorateResource::make($governorate),
        ]);
    }

    public function store(Governorate &$governorate, array $data): City
    {
        if (request()->hasFile('image')) {
            $data['image'] = request()->file('image')->storePublicly('governorates', 'public');
        }
        return $governorate->cities()->create($data);
    }
    public function update(City &$city, array $data): bool
    {
        if (request()->has('image') and $city->image and Storage::disk('public')->exists($city->image)) {
            Storage::disk('public')->delete($city->image);
        }
        if (request()->hasFile('image')) {
            $data['image'] = request()->file('image')->storePublicly('governorates', 'public');
        }
        return $city->update($data);
    }
    public function delete(City $city, $force): ?bool
    {
        if ($city->students()->exists()) {
            return error(__('messages.city_has_students'));
        }

        DB::transaction(function () use (&$city, $force) {
            if ($force) {
                return $city->forceDelete();
            } else {
                return $city->deleteOrFail();
            }
        });

        return success();
    }
}
