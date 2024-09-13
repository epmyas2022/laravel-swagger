<?php

namespace Laravel\Swagger\Types;


class BodyProperty
{

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
