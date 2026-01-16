<?php

namespace App\Http\Requests\Api\Admin\Question;

use App\Models\Question;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreQuestionRequest",
 *     type="object",
 * 
 *     @OA\Property(
 *         property="video",
 *         type="string",
 *         nullable=true,
 *         format="binary"
 *     ),
 *     @OA\Property(
 *         property="image",
 *         type="string",
 *         nullable=true,
 *         format="binary"
 *     ),
 *     @OA\Property(
 *         property="note_image",
 *         type="string",
 *         nullable=true,
 *         format="binary"
 *     ),
 * 
 *     @OA\Property(property="page_number", type="integer", example=1),
 *     @OA\Property(property="text", type="string", example="What is the capital of France?"),
 *     @OA\Property(property="note", type="string", example="This question is related to geography."),
 *     @OA\Property(property="degree", type="integer", example=1),
 * 
 *     @OA\Property(property="options[0][name]", type="string", example="false"),
 *     @OA\Property(property="options[0][is_true]", type="integer", example=0),
 *     @OA\Property(property="options[1][name]", type="string", example="true"),
 *     @OA\Property(property="options[1][is_true]", type="integer", example=1)
 * )
 */
class StoreQuestionRequest extends FormRequest
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
        $question = $this->route('question');

        return [
            'page_number' => ['nullable' , 'integer'] ,
            'text' => ['required', 'string'],
            'note' => ['nullable', 'string'],
            'degree' => ['integer'],

            //make the same login in u s exam and u question

            'video' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (!(is_string($value) || ($value instanceof \Illuminate\Http\UploadedFile && $value->getClientMimeType() === 'video/mp4'))) {
                        $fail('The ' . $attribute . ' must be a string or an mp4 file.');
                        return ;
                    }
                },
            ],
            
            'image' => [
                'nullable',
                'image' ,
                'mimes:png,jpg,jpeg' ,
            ],
            'note_image' => [
                'nullable',
                'image' ,
                'mimes:png,jpg,jpeg' ,
            ],

            //option to add
            'options' => ['required', 'array', 'min:2'],
            'options.*.name' => ['required'],
            'options.*.is_true' => ['required', 'boolean'],
        ];
    }
}
