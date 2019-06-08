<?php

namespace Nexmo\Laravel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class ValidSignatureRequest extends FormRequest {

    public function authorize() {
        return true;
    }

    public function rules() {
        return [
            'sig' => ['nexmo_signature']
        ];
    }

    protected function failedValidation(Validator $validator) {
        // We set an explicit response here to prevent the redirect 
        // that usually happens
        $response = response()->json('Signature is invalid', 422);
        throw new ValidationException($validator, $response);
    }
}
