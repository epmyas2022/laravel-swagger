<?php

namespace Laravel\Swagger\Attributes;

use Attribute;

#[Attribute()]
/**
 * Establer la sección de un conjunto de endpoints (se coloca en la clase de un controlador)
 */
class SwaggerSection
{
    public string $description;
    public ?string  $security;

    public function __construct(string $description, string $security = null)
    {
        $this->description = $description;
        $this->security = $security;
    }
}
