<?php

namespace Laravel\Swagger\Document;


abstract class SwaggerAttribute
{
    /**
     * Obtener atributos de una clase
     * @param string $attribute
     * @param \ReflectionClass $reflection
     */
    public static function getAttribute($attribute, $reflection): ?object
    {

        try {
            $attributes = $reflection->getAttributes($attribute);

            if (count($attributes) == 0) return null;

            return collect($attributes)->map(function ($attribute) {
                return $attribute->newInstance();
            })->first();
        } catch (\Error $e) {
            return null;
        }
    }


    /**
     * Obtener un objeto de atributos de una clase o metodo
     * @param array $attributes
     * @param string $method
     * @return object
     */

    public static function getAttributesMethod(array $attributes, $method): object
    {

        return (object) collect($attributes)->map(function ($attribute) use ($method) {

            return (array)self::getAttribute($attribute, $method);
        })->collapse()->all();
    }


  
}
