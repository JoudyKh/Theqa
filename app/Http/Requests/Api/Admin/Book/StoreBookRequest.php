<?php

namespace App\Http\Requests\Api\Admin\Book;

use App\Constants\Constants;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreBookRequest",
 *     type="object",
 *     required={"name", "image", "file"},
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Description of the book",
 *         example="A comprehensive guide to Laravel.",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="The name of the book",
 *         example="Introduction to Laravel"
 *     ),
 *     @OA\Property(
 *         property="image",
 *         type="string",
 *         format="binary",
 *         description="Cover image of the book",
 *         example="image_file.png"
 *     ),
 *     @OA\Property(
 *         property="file",
 *         type="string",
 *         format="binary",
 *         description="PDF file of the book",
 *         example="book_file.pdf"
 *     )
 * )
 */
class StoreBookRequest extends FormRequest
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
            'name' => ['required' , 'string' , 'max:255'] ,
            'image' => ['required' , 'image' , 'mimes:png,jpg,jpeg' ] , 
            'file' => ['required' , 'file' , 'mimes:pdf' ] ,
        ];
    }
}
