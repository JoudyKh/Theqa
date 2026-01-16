<?php

namespace App\Http\Requests\Api\Admin\Firebase;

use App\Models\User;
use App\Constants\Constants;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="BroadcastFirebaseRequest",
 *     type="object",
 *     @OA\Property(property="users", type="string",example="3,5", description="An array of user IDs."),
 *     @OA\Property(property="title", type="string", example="Black Honey and Butter Potatoes", description="The message to be sent. Maximum of 10,000 characters."),
 *     @OA\Property(property="body", type="string", example="Black Honey and Butter Potatoes", description="The message to be sent. Maximum of 10,000 characters."),
 * )
 */
class BroadcastFirebaseRequest extends FormRequest
{
    public function prepareForValidation()
    {
        if($this->has('users') and is_string($this->get('users'))){
            $this->merge([
                'users' => explode(',' , $this->get('users')) ,
            ]) ;
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return request()->is('*admin*');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'users' => [
                'array' ,
                'min:1' ,
                function($attribute , $value , $fail){
                    $idsCount = count($value) ;
                    $uniqueIds = array_unique($value) ;
                    if($idsCount != count($uniqueIds)){
                        $fail('duplicate values : ( ' . implode(' , ' , array_duplicate_values($value)) . ' )') ;
                        return ;
                    }

                    $databaseUsersIds = User
                    ::whereIn('id',$value)
                    ->whereHas('roles' , function($role){
                        $role->where('name' , Constants::STUDENT_ROLE);
                    })
                    ->pluck('id')
                    ->toArray();

                    $invalidUsersIds = array_diff($value , $databaseUsersIds);

                    if($invalidUsersIds and !empty($invalidUsersIds)){
                        $fail('invalid values : ( ' . implode(' , ' , $invalidUsersIds) . ' )') ;
                        return ;
                    }
                }
            ],

            'title' => ['required' ,'string' ,'max:50'] ,
            'body' => ['required' ,'string' ,'max:1000'] ,
        ];
    }
}
