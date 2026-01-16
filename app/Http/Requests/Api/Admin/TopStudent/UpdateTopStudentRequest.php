<?php

namespace App\Http\Requests\Api\Admin\TopStudent;

use Illuminate\Foundation\Http\FormRequest;
/**
 * @OA\Schema(
 *     schema="UpdateTopStudentRequest",
 *     type="object",
 *     @OA\Property(property="name", type="string", maxLength=255),
 *     @OA\Property(property="degree", type="integer", minimum=0, maximum=100),
 *     @OA\Property(property="description", type="string", maxLength=255),
 *     @OA\Property(property="image", type="string", format="binary"),  
 * )
 */
class UpdateTopStudentRequest extends FormRequest
{
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
            'name'  => ['string' , 'max:255'],
            'degree'  => ['integer' , 'min:0' , 'max:100'],
            'description' => ['string' , 'max:255'],
            'image' => ['image' , 'mimes:png,jpg,jpeg'],
        ];
    }
}
