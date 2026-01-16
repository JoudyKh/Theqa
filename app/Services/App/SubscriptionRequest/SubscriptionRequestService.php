<?php

namespace App\Services\App\SubscriptionRequest;

use Carbon\Carbon;
use App\Models\Coupon;
use App\Models\Section;
use Illuminate\Http\Request;
use App\Models\SubscriptionRequest;
use App\Enums\SectionStudentStatusEnum;
use App\Http\Requests\Api\App\SubscriptionRequest\CheckCouponRequest;
use App\Http\Requests\Api\App\SubscriptionRequest\CreateSubscriptionReqRequest;

class SubscriptionRequestService
{
    protected $user;

    public function __construct()
    {
        $this->user = request()->user();
    }

    public function checkCoupon(CheckCouponRequest &$request)
    {
        $section = Section::findOrFail($request->section_id);
        $coupon = Coupon::where('coupon', $request->coupon)->first();

        $errorMessage = null;
        $percentage = null;

        if (!$coupon) {
            $errorMessage = __('messages.coupon_does_not_exists');
        } elseif ($coupon->expires_at !== null and Carbon::parse($coupon->expires_at)->isPast()) {
            $errorMessage = __('messages.coupon_is_expired');
        } elseif ($coupon->usage_limit !== null and !$coupon->usage_limit) {
            $errorMessage = __('messages.coupon_limit_is_done');
        }

        if($errorMessage !== null){
            return success([
                'error_message' => $errorMessage,
            ]);
        }
        
        $sectionPriceAfterDiscount = ($section?->price ?? 0) - (($section?->price ?? 0) * (($section->discount ?? 0) / 100))  ;
        
        return success([
            'coupon_discount_percentage' => $coupon->discount_percentage,
            'section_price' => $section->price,
            'section_price_after_discount' => $sectionPriceAfterDiscount ,
            'result' => ($coupon->discount_percentage * $sectionPriceAfterDiscount) / 100,
        ]);
    }

    public function create(CreateSubscriptionReqRequest &$request)
    {
        $data = $request->validated();

        if($request->hasFile('image'))
        {
            $data['image'] = $request->file('image')->storePublicly("subscription-requests/images", "public");
        }
        $data['status'] = SectionStudentStatusEnum::PENDING->value;
        if($request->has('coupon')){
            $data['coupon_id'] = $request->input('coupon_id') ?? Coupon::where('coupon' , $request->get('coupon'))->first()?->id ;
        }
        return $this->user->subscriptionRequests()->create($data);
    }

    public function getUserRequests(Request $request)
    {
        $data = $this->user->subscriptionRequests();
        if ($request->status)
            $data->where('status', $request->status);
        return $data->paginate(config('app.pagination_limit'));
    }
}
