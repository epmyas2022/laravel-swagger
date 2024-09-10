<?php

#[Attribute()]
class SwaggerSummary {
    public string $summary;

    public function __construct(string $summary)
    {
        $this->summary = $summary;
    }
}