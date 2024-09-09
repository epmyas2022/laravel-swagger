<?php

namespace App\Http\Requests;

class ExampleRequest extends Request
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
            'name' => 'required|string',
            'schools' => 'required|array',
            'schools.*.name' => 'required|string',
            'schools.*.city.name' => 'required|string',
            'schools.*.array2' => 'required|array',
            'schools.*.array2.*.name' => 'required|string',
            'users.name' => 'required|string',



        ];
    }
}
