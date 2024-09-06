<?php

namespace App\Swagger\Types;
class ParamProperty
{

    public string $key;
    public bool $required;
    public string $in;

    public function __construct(string $key, bool $required, string $in = 'path')
    {
        $this->key = $key;
        $this->required = $required;
        $this->in = $in;
    }

    public function toObject(): object
    {
        return (object) [
            'name' => $this->key,
            'required' => $this->required,
            'in' => $this->in,
        ];
    }
}
