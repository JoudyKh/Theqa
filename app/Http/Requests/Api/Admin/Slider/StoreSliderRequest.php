<?php

namespace App\Http\Requests\Api\Admin\Slider;

use App\Models\Slider;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @OA\Schema(
 *     schema="StoreSliderRequest",
 *     type="object",
 *     required={"type"},
 *     @OA\Property(property="type", type="string",enum={"hero" , "locations" , "our_features"},default="hero"),
 *     @OA\Property(property="image", type="string", format="binary",nullable=true),  
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="phone", type="string"),
 * )
 */
class StoreSliderRequest extends FormRequest
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
        $rules = [
            'type' => ['bail' , 'required' , 'string' , Rule::in(array_keys(Slider::$types))] ,
        ];

        if(!$this->has('type') or !in_array($this->input('type') , array_keys(Slider::$types))){
            return $rules ;
        }
        $rules =  array_merge($rules , Slider::$types[$this->input('type')]['rules']['create']) ;
        return $rules ;
    }
}
