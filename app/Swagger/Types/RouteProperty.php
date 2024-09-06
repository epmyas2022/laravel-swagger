<?php
namespace App\Swagger\Types;

class RouteProperty {
    
    public string $method;
    public string $uri;
    public string $id;

    public function __construct(array $data)
    {

        $this->method = $data['method'];
        $this->uri = $data['uri'];
        $this->id = $data['id'];
    }

    public function toObject(): object
    {
        return (object) [
            'method' => $this->method,
            'uri' => $this->uri,
            'id' => $this->id,
        ];
    }

}