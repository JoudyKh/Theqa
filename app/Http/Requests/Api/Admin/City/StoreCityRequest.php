<?php

namespace App\Http\Requests\Api\Admin\City;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @OA\Schema(
 *     schema="StoreCityRequest",
 *     type="object",
 *     @OA\Property(property="name", type="string")
 * )
 */
class StoreCityRequest extends FormRequest
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
            'name'  => ['required' , 'string' , 'max:255'],
        ];
    }
}
