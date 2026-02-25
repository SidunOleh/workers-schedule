<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class ChangeUnavailableDaysRequest extends FormRequest
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
            'days' => 'array',
            'days.*' => 'array',
            'days.*.date' => 'required|date_format:Y-m-d',
            'days.*.unavailable' => 'required|boolean',
            'days.*.unavailable_from' => 'required_with:unavailable_to|date_format:H:i|nullable',
            'days.*.unavailable_to' => 'required_with:unavailable_from|date_format:H:i|nullable',
        ];
    }
}
