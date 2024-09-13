<?php

namespace src\Attributes;

use Attribute;

#[Attribute()]
class SwaggerResponse
{
    public ?string $description;
    public array $data;
    public int $status;
    public string $contentResponse;

    public function __construct(
        array $data,
        $status = 200,
        string $description = null,
        string $contentResponse = 'application/json'
    ) {
        $this->status = $status;
        $this->description = $description;
        $this->data = $data;
        $this->contentResponse = $contentResponse;
    }
}
