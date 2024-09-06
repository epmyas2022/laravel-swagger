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


#[Attribute()]
class SwaggerAuth
{

    public string $type = 'Bearer';

    public function __construct(string $type)
    {
        $this->type = $type;
    }
}

#[Attribute()]
class SwaggerContent {
    public string $contentBody;

    public function __construct(string $contentBody)
    {
        $this->contentBody = $contentBody;
    }
}

#[Attribute()]
class SwaggerResponse {
    public string $description;
    public array $content;
    public int $status;

    public function __construct(int $status, string $description, array $content)
    {
        $this->status = $status;
        $this->description = $description;
        $this->content = $content;
    }
}


#[Attribute()]
class SwaggerSummary {
    public string $summary;

    public function __construct(string $summary)
    {
        $this->summary = $summary;
    }
}