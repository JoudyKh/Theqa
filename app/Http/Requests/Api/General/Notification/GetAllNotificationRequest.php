<?php

namespace App\Http\Requests\Api\General\Notification;

use App\Models\User;
use App\Constants\Constants;
use Illuminate\Validation\Rule;
use App\Constants\Notifications;
use Illuminate\Foundation\Http\FormRequest;

class GetAllNotificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('sanctum')->check();
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $user = User::where('id' , auth('sanctum')->id())->first() ;

        if($user and !$user->hasAnyRole([Constants::ADMIN_ROLE])){
            $this->merge([
                'user_id' => $user->id ,
            ]) ;
        }

        $this->mergeIfMissing([
            'user_id' => $user->id ,
        ]) ;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_role' => ['bail', 'string' , Rule::in(Constants::ROLES_ARRAY)] ,
            'type' => ['bail' , 'string' , Rule::in(array_map(function($item) {
                return $item['TYPE'];
            }, Notifications::getAuthNotifications()))],

            'has_read' => ['boolean'] ,
            'is_broadcast' => ['boolean'] ,
            'user_id' => [
                'integer' , Rule::exists('users' , 'id')
                ->whereNull('deleted_at') ,
            ],
        ];
    }
}
