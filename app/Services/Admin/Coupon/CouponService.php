<?php

namespace App\Services\Admin\Coupon;

use App\Http\Resources\CouponResource;
use App\Models\Coupon;
use App\Models\Offer;
use App\Http\Resources\OfferResource;
use Illuminate\Support\Facades\Storage;

class CouponService
{
    public function __construct()
    {
    }

    public function getAll($trashOnly)
    {
        $coupons = Coupon::orderByDesc($trashOnly ? 'deleted_at' : 'created_at');

        if ($trashOnly) {
            $coupons->onlyTrashed();
        }
        $coupons = $coupons->paginate(config('app.pagination_limit'));
        return CouponResource::collection($coupons);
    }

    public function store(array $data): Coupon
    {
        return Coupon::create($data);
    }

    public function update(Coupon &$coupon, array $data): bool
    {
        $coupon->update($data);

        return true;
    }

    public function delete(Coupon $coupon, $force): ?bool
    {
        if ($force) {
            return $coupon->forceDelete();
        }
        return $coupon->deleteOrFail();
    }
}
