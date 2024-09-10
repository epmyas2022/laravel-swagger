<?php
#[Attribute()]
class SwaggerAuth
{

    public string $auth;

    public function __construct(string $auth = 'bearerAuth')
    {
        $this->$auth = $auth;
    }
}
