<?php

namespace Laravel\Swagger\Attributes;
use Laravel\Swagger\Constants\ContentType;
use Attribute;

#[Attribute()]
class SwaggerResponseFile
{
    public ?string $descriptionResFile;
    public int $statusResFile;
    public string $contentResponseFile;

    public function __construct(
        string $contentResponse = ContentType::BINARY,
        $status = 200,
        string $description = 'Example Response',
    ) {
        $this->statusResFile = $status;
        $this->descriptionResFile = $description;
        $this->contentResponseFile = $contentResponse;
    }
}
