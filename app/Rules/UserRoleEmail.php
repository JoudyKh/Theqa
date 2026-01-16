<?php

namespace App\Rules;

use Closure;
use App\Models\User;
use App\Constants\Constants;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Validation\ValidationRule;

class UserRoleEmail implements ValidationRule
{
    public function __construct(protected string|int|null $authId = null){}
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        // Query to check if the email exists and belongs to a user with the role named 'user'
        $exists = User::where('email', $value)
            ->when($this->authId , function($query){
                $query->where('id' , $this->authId);
            })
            ->whereHas('roles', function ($q) {
                $q->where('name', Constants::STUDENT_ROLE);
            })
            ->exists();

        if (!$exists) {
            $fail(__('messages.email_not_found'));
        }
    }
}
