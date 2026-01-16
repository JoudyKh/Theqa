<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="CouponResource",
 *     type="object",
 *     title="Coupon",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="The unique identifier of the Coupon."
 *     ),
 *     @OA\Property(
 *         property="coupon",
 *         type="string",
 *         description="The coupon text."
 *     ),
 *     @OA\Property(
 *         property="discount_percentage",
 *         type="string",
 *         description="the discount percentage"
 *     ),
 *     @OA\Property(
 *         property="expires_at",
 *         type="date",
 *     ),
 *     @OA\Property(
 *         property="usage_limit",
 *         type="string",
 *     ),
 *     @OA\Property(
 *         property="deleted_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the coupon was soft deleted. Null if not deleted."
 *     )
 * )
 */
class CouponResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Pagination\AbstractPaginator
     */
    public static function collection($data)
    {
        /*
        This simply checks if the given data is and instance of Laravel's paginator classes
         and if it is,
        it just modifies the underlying collection and returns the same paginator instance
        */
        if (is_a($data, \Illuminate\Pagination\AbstractPaginator::class)) {
            $data->setCollection(
                $data->getCollection()->map(function ($listing) {
                    return new static($listing);
                })
            );
            return $data;
        }

        return parent::collection($data);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
