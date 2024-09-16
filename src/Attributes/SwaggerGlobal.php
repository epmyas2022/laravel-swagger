<?php
namespace Laravel\Swagger\Attributes;

use Attribute;

#[Attribute()]
class SwaggerGlobal {
    public array $attributes;

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

}