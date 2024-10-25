<?php

namespace Laravel\Swagger\Document\Trait;

use Laravel\Swagger\Document\Types\BodyProperty;
use Laravel\Swagger\Document\Types\ParamProperty;
use Laravel\Swagger\Document\Types\RouteProperty;
use Illuminate\Support\Arr;


trait SwaggerDocument
{

    public function setSchemaPath(array $sections, $value)
    {
        $sections = collect($sections);

        $this->schema = Arr::add($this->schema, "paths.{$sections->implode('.')}",  $value);
    }


    public function setComponent($key, BodyProperty $value)
    {
        Arr::set($this->schema, "components.schemas.$key", $value->getProperties());
    }

    public function setSchemaParameters(RouteProperty $routeProperty, ParamProperty $parameter)
    {
        $subArray = Arr::get($this->schema, "paths.{$routeProperty->id}.parameters", []);

        $subArray[] = $parameter->toObject();

        Arr::set($this->schema, "paths.{$routeProperty->id}.parameters", $subArray);
    }

    public function setSchemaBody(RouteProperty $routeProperty, $class, $content)
    {

        $this->setSchemaPath(
            [$routeProperty->id, 'requestBody', 'content', $content, 'schema', '$ref'],
            "#/components/schemas/$class"
        );
    }


    public function setSchemaSecurity(RouteProperty $routeProperty, $security)
    {
        $securityArray = Arr::get($this->schema, "paths.{$routeProperty->id}.security", []);

        $securityArray[] = $security;

        $this->setSchemaPath([$routeProperty->id, 'security'], $securityArray);
    }


    public function setSchemaResponse(RouteProperty $routeProperty, $attributes)
    {
        if (isset($attributes->data)) {

            $data = $attributes->data;
            $isArray = $this->isTypeArray($attributes->data);

            $this->setSchemaPath([$routeProperty->id, 'responses'], [
                $attributes->status => [
                    'description' => $attributes->description ?? 'Example Response',
                    'content' => [
                        $attributes->contentResponse => [
                            'schema' =>
                            $isArray ?
                                collect($this->transformDataResponse($data))->first()
                                : [
                                    'type' => 'object',
                                    'properties' => $this->transformDataResponse($data)
                                ]

                        ]
                    ]
                ]
            ]);
        }

        $this->setSchemaPath([$routeProperty->id, 'responses'], [
            '200' => ['description' => 'Success Response'],
            '400' => ['description' => 'Error Bad Request',],
            '500' => ['description' => 'Internal Server Error',]
        ]);
    }



    private function format($data, $force_object = true)
    {
        if (gettype($data) === 'array' && $force_object)
            return json_decode(json_encode($data, JSON_FORCE_OBJECT), true);

        return json_decode(json_encode($data));
    }

    private function isTypeArray($value)
    {
        $value = json_decode(json_encode($value));

        return gettype($value) == 'array';
    }

    private function transformDataResponse($data)
    {
        $data =  $this->format($data, false);

        $content = [];


        collect($data)->each(function ($value, $key) use (&$content) {
            $type = gettype($value);

            if ($type == 'object')
                return  Arr::set($content, $key, [
                    'type' => 'object',
                    'properties' => $this->transformDataResponse($value)
                ]);

            if ($type == 'array')
                return  Arr::set($content, $key, [
                    'type' => 'array',
                    'items' => $this->transformDataResponse($value)->first()
                ]);


            Arr::set($content, $key, ['type' => $type, 'example' => $value]);
        });


        return collect($content);
    }
}
