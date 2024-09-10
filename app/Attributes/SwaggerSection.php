<?php

namespace App\Attributes;

use Attribute;

#[Attribute()]
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