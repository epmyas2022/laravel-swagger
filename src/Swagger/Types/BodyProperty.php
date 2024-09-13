<?php

namespace src\Swagger\Types;

use src\Swagger\Trait\SwaggerConvertValidation;

class BodyProperty
{

    use SwaggerConvertValidation;

    public string $key;
    public object $properties;

    public function __construct(object $properties)
    {
        $this->properties = $properties;
    }


    /**
     * Get the type of the property
     * @return object
     */
    public function getProperties(): object
    {

        return $this->properties;
    }
}
