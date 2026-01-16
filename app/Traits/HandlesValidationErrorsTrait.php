<?php
// app/Traits/HandlesValidationErrorsTrait.php

namespace App\Traits;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

trait HandlesValidationErrorsTrait
{
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();

        throw new HttpResponseException(response()->json(
            ['message' => $errors[0] . ((count($errors) > 1) ? __('messages.more_errors', ['number' => count($errors) - 1]) : ''),
                'errors' => $errors,]
            , 422));
    }
}
