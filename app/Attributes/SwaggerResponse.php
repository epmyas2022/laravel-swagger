<?php

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
