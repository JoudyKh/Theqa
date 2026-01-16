<?php

namespace App\Http\Requests\Api\Admin\Book;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateBookRequest",
 *     type="object",
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Description of the book",
 *         example="An updated description of the book.",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="The updated name of the book",
 *         example="Updated Book Title"
 *     ),
 *     @OA\Property(
 *         property="image",
 *         type="string",
 *         format="binary",
 *         description="Updated cover image of the book",
 *         example="updated_image.png"
 *     ),
 *     @OA\Property(
 *         property="file",
 *         type="string",
 *         format="binary",
 *         description="Updated PDF file of the book",
 *         example="updated_book.pdf"
 *     )
 * )
 */
class UpdateBookRequest extends FormRequest
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
            'description'=>['nullable' , 'string'] ,
            'name' => ['string' , 'max:255'] ,
            'image' => ['image' , 'mimes:png,jpg,jpeg' ] , 
            'file' => ['file' , 'mimes:pdf' ] ,
        ];
    }
}
