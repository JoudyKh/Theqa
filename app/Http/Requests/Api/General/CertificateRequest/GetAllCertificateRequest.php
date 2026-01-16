<?php

namespace App\Http\Requests\Api\General\CertificateRequest;

use App\Models\User;
use App\Constants\Constants;
use Illuminate\Validation\Rule;
use App\Enums\CertificateRequestStatusEnum;
use Illuminate\Foundation\Http\FormRequest;

class GetAllCertificateRequest extends FormRequest
{
    protected ?User $currUser = null ;
    public function prepareForValidation()
    {
        $this->currUser = User::with(['roles'])->where('id' , auth('sanctum')->id())->first() ;

        if($this->currUser?->hasRole(Constants::STUDENT_ROLE))
        {
            $this->merge([
                'student_id' => $this->currUser->id ,
            ]);
        }
        
    }
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
            'student_id' => ['integer' , 'exists:users,id',] , 
            'course_id' => ['integer' , 'exists:sections,id,type,'.Constants::SECTION_TYPE_COURSES,] ,
            'accepted' => ['boolean'] , 
            'rejected' => ['boolean'] , 
            'status' => ['string' , Rule::in(CertificateRequestStatusEnum::all()),],
        ];
    }
}
