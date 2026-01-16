<?php

namespace App\Http\Requests\Api\App\CertificateRequest;

use App\Models\User;
use App\Constants\Constants;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreCertificateRequestRequest",
 *     type="object",
 *     @OA\Property(
 *         property="course_id",
 *         type="integer",
 *         description="The ID of the course",
 *         example=2
 *     ),
 * )
 **/
class StoreCertificateRequestRequest extends FormRequest
{
        protected ?User $currUser = null;

        public function prepareForValidation()
        {
                $this->currUser = User::with(['roles'])->where('id' , auth('sanctum')->id())->first();

                if($this->currUser?->hasRole(Constants::STUDENT_ROLE))
                {
                        $this->merge([
                                'student_id' => $this->currUser->id ,
                        ]) ;
                }
        }
        /**
         * Determine if the user is authorized to make this request.
         */
        public function authorize(): bool
        {
                return $this->currUser?->hasRole(Constants::STUDENT_ROLE);
        }

        /**
         * Get the validation rules that apply to the request.
         *
         * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
         */
        public function rules(): array
        {
                //todo validation for finishing the course
                return [
                        'course_id' => [
                                'bail',
                                'required',
                                'integer',
                                Rule::exists('sections', 'id')
                                        ->whereNull('deleted_at'),
                                Rule::unique('certificate_requests', 'course_id')
                                        ->whereNull('deleted_at')
                                        ->where('student_id' , $this->currUser->id),
                        ],
                ];
        }

        public function messages(): array
        {
                return [
                        'course_id.unique'=>__('messages.request_already_sent')
                ];
        }
}