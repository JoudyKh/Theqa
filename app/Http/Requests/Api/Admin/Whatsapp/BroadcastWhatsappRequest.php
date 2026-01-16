<?php

namespace App\Http\Requests\Api\Admin\Whatsapp;

use App\Models\User;
use App\Constants\Constants;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="BroadcastWhatsappRequest",
 *     type="object",
 *     @OA\Property(property="governorate_id", type="integer", description="The ID of the governorate. Must exist in the governorate table and not be soft deleted."),
 *     @OA\Property(property="city_id", type="integer", description="The ID of the city. Must exist in the city table and not be soft deleted."),
 *     @OA\Property(property="role", type="string", enum={"student", "admin"}, example="student", description="The role of the recipient (e.g., student or admin). Defaults to 'student' if not provided."),
 *     @OA\Property(property="phone_type", type="string", enum={"phone_number", "family_phone_number" , "both"}, example="family_phone_number", description="The type of phone to use (e.g., phone_number or family_phone_number)."),
 *     @OA\Property(property="users", type="string",example="3,5", description="An array of user IDs."),
 *     @OA\Property(property="message", type="string", example="Black Honey and Butter Potatoes", description="The message to be sent. Maximum of 10,000 characters.")
 * )
 */
class BroadcastWhatsappRequest extends FormRequest
{
    public function prepareForValidation()
    {
        $this->mergeIfMissing([
            'role' => 'student',
        ]);

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
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'governorate_id' => ['integer', Rule::exists('governorates', 'id')->whereNull('deleted_at')],
            'city_id' => ['integer', Rule::exists('cities', 'id')->whereNull('deleted_at')],
            'role' => ['required', 'string', Rule::in(['student', 'admin'])],
            'phone_type' => ['required', 'string', Rule::in(['phone_number', 'family_phone_number' , 'both'])],
            'users' => ['array' , 'min:1'],

            'users.*' => [
                'required',
                'integer',
                function($attribute , $value , $fail){
                    $user = User::query() ;

                    $user = $user->findOrFail($value) ;

                    if($this->has('role') and !$user->hasRole($this->get('role'))){
                        $fail("user with id $value dose not have the role {$this->get('role')}") ;
                    }

                },
            ],

            'message' => ['required' ,'string' ,'max:10000'] ,
        ];
    }
}
