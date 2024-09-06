<?php

namespace App\Traits;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

trait Validate
{

    /**
     * Indica si la validacion debe detenerse despues de la primera regla fallida.
     * @var bool
     */
    protected $stopOnFirstFailure = false;


    /**
     * Manejar los errores de la validacion
     * @param $validator
     * @param int $code
     * @throws HttpResponseException
     */
    public function handlerError($validator, $code = Response::HTTP_UNPROCESSABLE_ENTITY)
    {
        throw new HttpResponseException(response()->json(
            ['message' => collect($validator->errors())->flatMap(fn ($error) => $error)],
            $code
        ));
    }


    /**
     * Validar los datos de la peticion
     * @param $request
     * @param array $rules
     * @param mixed ...$params
     * @return mixed
     * @throws HttpResponseException
     */
    public function validateData($request, $rules = [], ...$params): mixed
    {
        $validator = Validator::make($request->all(), $rules, ...$params)
            ->stopOnFirstFailure($this->stopOnFirstFailure);

        if ($validator->fails()) {
            return $this->handlerError($validator);
        }

        return $validator->validated();
    }


    /**
     * Validar los datos de la ruta
     * @param array $rules
     * @param mixed ...$params
     * @return mixed
     * @throws HttpResponseException
     */
    public function validatePath($rules, ...$params): mixed
    {
        $data = request()->route()->parameters();

        $validator = Validator::make($data, $rules, ...$params)
            ->stopOnFirstFailure($this->stopOnFirstFailure);

        if ($validator->fails()) {
            $this->handlerError($validator);
        }

        return $validator->validated();
    }
}
