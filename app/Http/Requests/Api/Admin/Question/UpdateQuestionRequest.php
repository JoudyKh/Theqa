<?php

namespace App\Http\Requests\Api\Admin\Question;

use App\Models\Question;
use App\Models\StudentAnswer;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;


/**
 * @OA\Schema(
 *     schema="UpdateQuestionRequest",
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
 * 
 *     @OA\Property(
 *         property="note_image",
 *         type="string",
 *         nullable=true,
 *         format="binary"
 *     ),
 *
 *     @OA\Property(
 *         property="text",
 *         type="string",
 *         description="The text content of the question."
 *     ),
 *    @OA\Property(
 *         property="note",
 *         type="string",
 *         description="Some note."
 *     ),
 *     @OA\Property(
 *         property="degree",
 *         type="integer",
 *         description="The degree of the question."
 *     ),
 *     @OA\Property(property="page_number", type="integer", example=1),
 *
 *     @OA\Property(
 *         property="update_options",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *            @OA\Property(
 *                 property="id",
 *                 type="integer",
 *                 description="1"
 *             ),
 *             @OA\Property(
 *                 property="name",
 *                 type="string",
 *                 description="The text content of the option."
 *             ),
 *             @OA\Property(
 *                 property="is_true",
 *                 type="boolean",
 *                 description="Indicates if the option is correct."
 *             )
 *         ),
 *         description="Array of options for the question."
 *     ),
 *
 *     @OA\Property(property="update_options[0][id]", type="integer", example=1),
 *     @OA\Property(property="update_options[0][name]", type="string", example="false"),
 *     @OA\Property(property="update_options[0][is_true]", type="integer", example=0),
 *
 *     @OA\Property(property="update_options[1][id]", type="integer", example=1),
 *     @OA\Property(property="update_options[1][name]", type="string", example="false"),
 *     @OA\Property(property="update_options[1][is_true]", type="integer", example=0),
 *
 *     @OA\Property(property="options[0][name]", type="string", example="false"),
 *     @OA\Property(property="options[0][is_true]", type="integer", example=0),
 *     @OA\Property(property="options[1][name]", type="string", example="true"),
 *     @OA\Property(property="options[1][is_true]", type="integer", example=1),
 *
 *     @OA\Property(property="trash_options[0]",type="integer",nullable=true)
 * )
 */

class UpdateQuestionRequest extends FormRequest
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

        $optionsCount = $question->options()->count();

        return [
            'page_number' => ['nullable', 'integer'],
            'text' => ['string'],
            'note' => ['string'],
            'degree' => ['integer'],

            'video' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (!(is_string($value) || ($value instanceof \Illuminate\Http\UploadedFile && $value->getClientMimeType() === 'video/mp4'))) {
                        $fail('The ' . $attribute . ' must be a string or an mp4 file.');
                    }
                },
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:png,jpg,jpeg',
            ],

            'note_image' => [
                'nullable',
                'image',
                'mimes:png,jpg,jpeg',
            ],

            //option to update
            'update_options' => ['array'],
            'update_options.*.is_true' => ['required', 'boolean'],
            'update_options.*.name' => ['required'],
            'update_options.*.id' => [
                'required',
                'integer',
                'distinct',
                'exists:options,id,question_id,' . $question->id,
            ],

            //option to add
            'options' => ['array'],
            'options.*.name' => ['required'],
            'options.*.is_true' => ['required', 'boolean'],


            //option to delete
            'trash_options' => [
                'array',
                function ($attribute, $value, $fail) use ($optionsCount) {
                    $trashed = count(request()->input('trash_options', []));
                    $old = $optionsCount;
                    $new = count(request()->input('options', []));

                    if ($old + $new - $trashed < 2) {
                        $fail(__('messages.options_less_than_2'));
                    }
                },
            ],
            'trash_options.*' => [
                'required',
                'integer',
                Rule::exists('options', 'id')
                    ->whereNull('deleted_at')
                    ->where('question_id', $question->id),


                function ($attribute, $value, $fail) {
                    $student_answers = StudentAnswer::query();

                    $student_answers->where('option_id', $value);

                    if ($student_answers->exists()) {
                        $fail(__('messages.student_already_choose_this_option'));
                    }
                }
            ],
        ];
    }
}
