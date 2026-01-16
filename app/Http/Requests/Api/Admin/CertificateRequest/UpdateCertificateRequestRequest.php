<?php

namespace App\Http\Requests\Api\Admin\CertificateRequest;

use Illuminate\Validation\Rule;
use App\Enums\CertificateRequestStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @OA\Schema(
 *     schema="UpdateCertificateRequestRequest",
 *     type="object",
 *     @OA\Property(property="status", type="string", enum={"pending","rejected","accepted"},nullable=true),
 *     @OA\Property(property="file", type="string",format="binary"),
 *     @OA\Property(property="note", type="string", maxLength=255)
 * )
 */
class UpdateCertificateRequestRequest extends FormRequest
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
            'status' => ['string' , Rule::in(CertificateRequestStatusEnum::all())] ,
            'file' => [
                Rule::requiredIf($this->has('status') and $this->input('status') == CertificateRequestStatusEnum::ACCEPTED) ,
                'nullable' , 
                'file' ,
            ] ,
            'note' => ['nullable' , 'string'] ,
        ];
    }
}
