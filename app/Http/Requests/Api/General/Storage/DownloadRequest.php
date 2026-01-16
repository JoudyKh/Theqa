<?php

namespace App\Http\Requests\Api\General\Storage;

use Storage;
use Illuminate\Foundation\Http\FormRequest;

class DownloadRequest extends FormRequest
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
            'file_path' => [
                'required' ,
                'string' , 
                function($attribute , $value , $fail){
                    if(!Storage::disk('public')->exists($value)){
                        $fail('file does not exists') ;
                        return ;
                    }
                }
            ] ,
            'file_name' => [
                'string',
                'min:3'
            ] ,
        ];
    }
}
