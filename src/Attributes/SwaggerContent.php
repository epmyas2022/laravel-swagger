<?php

namespace src\Attributes;

use Attribute;
#[Attribute()]
class SwaggerContent {
    public string $contentBody;

    public function __construct(string $contentBody)
    {
        $this->contentBody = $contentBody;
    }
}