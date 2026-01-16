<?php

namespace App\Services\General\Offer;
use App\Models\Offer;
use App\Http\Resources\OfferResource;

class OfferService
{
    public function getAll()
    {
        $offers = Offer::orderByDesc('created_at')->paginate(config('app.pagination_limit'));
        return OfferResource::collection($offers);
    }
}