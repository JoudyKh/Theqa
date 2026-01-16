<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     */
    public static function collection($data): AbstractPaginator|AnonymousResourceCollection
    {
        /*
        This simply checks if the given data is and instance of Laravel's paginator classes
         and if it is,
        it just modifies the underlying collection and returns the same paginator instance
        */
        if (is_a($data, AbstractPaginator::class)) {
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
        $notification = $this->asNotification();

        $notification->setAttribute('data', $this);
        $notification->setAttribute('user', $this->user);

        return [
            'id' => $this->id,
            'title' => $notification->getTitle(),
            'description' =>$notification->getBody(),
            'params' => $notification->getParams() ,
            'has_read' => $this->has_read,
            'type' => $this->type,
            'state' => $this->state,
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', fn() => UserRecourse::make($this->user), null),
            'is_broadcast' => $this->is_broadcast,
            'created_at' => $this->created_at,
        ];
    }
}
