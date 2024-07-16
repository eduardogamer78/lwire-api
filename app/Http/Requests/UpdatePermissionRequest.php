<?php

declare(strict_types=1);

namespace App\Http\Requests;

class UpdatePermissionRequest extends PermissionRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        return $rules;
    }
}
