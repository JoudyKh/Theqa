<?php

namespace App\Http\Requests\Api\Admin\Section;

use App\Constants\Constants;
use Illuminate\Foundation\Http\FormRequest;
use App\Traits\HandlesValidationErrorsTrait;

class StoreSectionRequest extends FormRequest
{
    // use HandlesValidationErrorsTrait;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation()
    {
        //trim(null) returns "" not null 
        $this->merge([
            'type' => $this->route('type') ? trim($this->route('type')) : 'super',
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return array_merge(
            Constants::SECTIONS_TYPES[$this->type]['rules']['create'],
            ['type' => 'in:' . implode(',', array_keys(Constants::SECTIONS_TYPES))]
        );
    }
}