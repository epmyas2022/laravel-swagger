<?php

namespace src\Attributes;

use Attribute;
#[Attribute()]
/**
 * Establece el resumen de un endpoint (se coloca en el método de un controlador)
 */
class SwaggerSummary
{
    public string $summary;

    public function __construct(string $summary)
    {
        $this->summary = $summary;
    }
}
