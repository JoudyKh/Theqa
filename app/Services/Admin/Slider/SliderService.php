<?php

namespace App\Services\Admin\Slider;

use App\Models\Slider;
use App\Http\Resources\SliderResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Api\Admin\Slider\StoreSliderRequest;
use App\Http\Requests\Api\Admin\Slider\UpdateSliderRequest;

class SliderService
{
    public function __construct()
    {
    }
    public function getAll($trashOnly)
    {
        $sliders = Slider::orderByDesc($trashOnly ? 'deleted_at' : 'created_at');

        if ($trashOnly) {
            $sliders->onlyTrashed();
        }

        if (request()->query('type')) {
            $sliders->where('type', request()->query('type'));
        }

        $sliders = $sliders->paginate(config('app.pagination_limit'));
        return SliderResource::collection($sliders);
    }

    public function store(StoreSliderRequest &$request)
    {
        $data = $request->validated() ;
        try {
            return DB::transaction(function () use (&$request,&$data) {

                if ($request->hasFile('image')) {
                    $data['image'] = $request->file('image')->storePublicly('sliders', 'public');
                }

                return Slider::create($data);
            });

        } catch (\Throwable $th) {
            if (isset($data['image']) and Storage::disk('public')->exists($data['image'])) {
                Storage::disk('public')->delete($data['image']);
            }
            throw $th;
        }
    }
    public function update(Slider &$slider, UpdateSliderRequest &$request): bool
    {
        $data = $request->validated() ;
        try {
            return DB::transaction(function () use (&$slider,&$request,&$data) {

                $old_image = $slider->image;

                if ($request->has('image')) {
                    if ($request->hasFile('image'))
                        $data['image'] = $request->file('image')->storePublicly('sliders', 'public');
                    else
                        $data['image'] = null;
                }

                $slider->update($data);

                DB::afterCommit(function () use ($old_image,&$request) {
                    if ($request->has('image') and $old_image and Storage::disk('public')->exists($old_image)) {
                        Storage::disk('public')->delete($old_image);
                    }
                });

                return true;
            });
        } catch (\Throwable $th) {
            if (isset($data['image']) and Storage::disk('public')->exists($data['image'])) {
                Storage::disk('public')->delete($data['image']);
            }
            throw $th;
        }
    }
    public function delete(Slider $slider, $force): ?bool
    {
        if ($force) {
            return $slider->forceDelete();
        }
        return $slider->deleteOrFail();
    }
}
