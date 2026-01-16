<?php

namespace App\Services\Admin\Offer;
use App\Models\Offer;
use App\Http\Resources\OfferResource;
use Illuminate\Support\Facades\Storage;

class OfferService
{
    public function __construct(){}
    public function getAll($trashOnly)
    {
        $offers = Offer::orderByDesc($trashOnly ? 'deleted_at' : 'created_at');

        if ($trashOnly) {
            $offers->onlyTrashed();
        }
        $offers = $offers->paginate(config('app.pagination_limit'));
        return OfferResource::collection($offers);
    }

    public function store(array $data):Offer
    {
        if(request()->hasFile('image')){
            $data['image'] = request()->file('image')->storePublicly('offers' , 'public') ;
        }
        return Offer::create($data) ;
    }
    public function update(Offer &$offer , array $data):bool
    {
        if(request()->has('image') and $offer->image and Storage::disk('public')->exists($offer->image)){
            Storage::disk('public')->delete($offer->image) ;
        }
        if(request()->hasFile('image')){
            $data['image'] = request()->file('image')->storePublicly('offers' , 'public') ;
        }
        $offer->update($data) ;

        return true;
    }
    public function delete(Offer $offer , $force):?bool
    {
        if($force){
            return $offer->forceDelete() ;
        }
        return $offer->deleteOrFail() ;
    }
}
