<?php

namespace App\Http\Requests;

use App\Traits\Validate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Symfony\Component\HttpFoundation\Response;

/**
 * Define the base derived class FormRequest
 * @version 1.0.0
 * @package App\Http\Requests
 */
class Request extends FormRequest
{

    use Validate;
    /**
     * Indicates whether validation should stop after the first rule failure.
     * @var bool
     */

    /**
     * The validation rules aditionals that apply to the request.
     * @return array
     */
    protected $rulesAditionals = [];
    /**
     * Get the validation rules that apply to the request.
     * @param ValidatorContract $validator
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    public function failedValidation(ValidatorContract $validator): void
    {
        $this->handlerError($validator);
    }
    /**
     * Add additional rules to the request
     * @param array $rules
     * @return self
     */
    public function additionalRules(array $rules)
    {
        $this->rulesAditionals = $rules;
        return $this;
    }

    /**
     * Get the request method
     * @return string
     */
    public function method()
    {
        return request()->method();
    }

    /**
     * Get the request method
     * @param string $method
     * @return string required | nullable
     */
    public function requiredIfMethod(string $method)
    {
        return $this->method() == $method ? 'required' : 'nullable';
    }

    protected function validationRules()
    {

        $rules = method_exists($this, 'rules') ? $this->container->call([$this, 'rules']) : [];

        return array_merge($rules, $this->rulesAditionals);
    }


    /**
     * Validate the data of the request
     * @param array $rules
     * @param mixed ...$params
     * @return mixed
     */
    public function validate($rules, ...$params)
    {
        return $this->validateData($this, $rules, ...$params);
    }
}
