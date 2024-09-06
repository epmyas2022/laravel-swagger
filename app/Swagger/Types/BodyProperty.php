<?php

namespace App\Swagger\Types;

use App\Swagger\Trait\SwaggerConvertValidation;
use Illuminate\Support\Collection;

class BodyProperty
{

    use SwaggerConvertValidation;

    public string $key;
    public object $properties;
    public bool $required;
    public string $content;

    public function __construct(string $key, object $properties, bool $required, ?string $content)
    {
        $this->key = $key;
        $this->properties = $properties;
        $this->required = $required;
        $this->content = $content ?? 'application/json';
    }


    /**
     * Get the type of the property
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties->{$this->key};
    }
  


}
