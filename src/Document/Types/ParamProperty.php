<?php

namespace Laravel\Swagger\Document\Types;


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

    public static function setObject(object $object): self
    {
        return new self($object->key, $object->required, $object->in);
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
