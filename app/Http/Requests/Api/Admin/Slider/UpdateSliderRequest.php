<?php

namespace App\Http\Requests\Api\Admin\Slider;

use App\Models\Slider;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @OA\Schema(
 *     schema="UpdateSliderRequest",
 *     type="object",
 *     @OA\Property(property="image", type="string", format="binary",nullable=true),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="phone", type="string"),
 * )
 */
class UpdateSliderRequest extends FormRequest
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
        $slider = $this->route('slider') ;

        return Slider::$types[$slider->type]['rules']['update'] ;
    }
}
