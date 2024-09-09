<?php

namespace App\Swagger\Types;

use App\Swagger\Trait\SwaggerConvertValidation;
use Illuminate\Support\Collection;

class BodyProperty
{

    use SwaggerConvertValidation;

    public string $key;
    public object $properties;
    public array $requiredFields;

    public function __construct(string $key, object $properties, array $required)
    {
        $this->key = $key;
        $this->properties = $properties;
        $this->requiredFields = $required;
    }


    /**
     * Get the type of the property
     * @return array
     */
    public function getProperties(): array
    {

        return $this->properties->content[$this->key];
    }

  


}
