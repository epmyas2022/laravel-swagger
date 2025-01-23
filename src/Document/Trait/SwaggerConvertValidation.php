<?php

namespace Laravel\Swagger\Document\Trait;

use Laravel\Swagger\Document\Types\BodyProperty;
use Laravel\Swagger\Document\Types\ParamProperty;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;


trait SwaggerConvertValidation
{

    /**
     * Define the types of data for the validation rules  (types validator laravel vs swagger types)
     * @return Collection
     */
    public function getVarsList(): Collection
    {
        return collect([
            'decimal' => 'number',
            'numeric' => 'number',
            'double' => 'number',
            'float' => 'number',
            'int' => 'integer',
            'integer' => 'integer',
            'string' => 'string',
            'array' => 'array',
            'bool' => 'boolean',
            'boolean' => 'boolean',
            'object' => 'object',
            'email' => 'string',
            'mixed' => 'string',
            'array' => 'array',
            'file' => function ($key, $params) {
                $key = str_replace('items.properties.', 'items',  $key);
                return (object)[
                    'key' => $key,
                    'type' => [
                        'type' => 'string',
                        'format' => 'binary',
                    ]
                ];
            },
            'in' => function ($key, $params) {
                return (object)[
                    'key' => $key,
                    'type' => [
                        'type' => 'string',
                        'enum' => $params,
                    ]
                ];
            },
        ]);
    }




    /**
     * Get the params of the rule
     * @param string $rule
     * @return object
     */
    public function getParamsRule($rule): ?object
    {

        if(gettype($rule) == 'object') return null;

        return match (true) {
            Str::contains($rule, ':') => (object) [
                'key' => explode(':', $rule)[0],
                'values' =>  explode(',', explode(':', $rule)[1])
            ],
            default => (object)
            [
                'key' => $rule,
                'values' => null
            ],
        };
    }
    /**
     * Convert the rules to a collection
     * @param array|string $rules
     * @return
     */
    public function collectRules($rules): Collection
    {
        return is_array($rules) ? collect($rules) : collect(explode('|', $rules));
    }
    public function getTypes(Collection $rules): Collection
    {
        $typesVars = $this->getVarsList();

        return  $rules->map(
            function ($rule) use ($typesVars) {
                $params = $this->getParamsRule($rule);
                return $typesVars->keys()->contains($params?->key) ? (object)
                [
                    'params' => $params->values,
                    'rule' => $typesVars->get($params->key),
                ] : null;
            }
        )->whereNotNull();
    }



    /**
     * Mapper the name of the key to swagger format
     * @param string $key
     * @return string
     */
    public function mapperKey($key)
    {

        $key = preg_replace('/(?<!\*)\.(?!\*)/', '.properties.', $key);

        $key = preg_replace('/(\.\*\.)|(\.\*)/', '.items.properties.', $key);

        return $key;
    }


    /**
     * Transform the rules to swagger format body
     * @param Collection $rules
     * @return object
     */
    public function transformToSwagger(Collection $rules): BodyProperty
    {

        $grouped = $rules->collapse();
        $content = [];
        $fieldsRequired = [];

        $grouped->each(function ($validation, $key) use (&$content, &$fieldsRequired) {

            $rules = $this->collectRules($validation);
            $types = $this->getTypes($rules);

            $key = $this->mapperKey($key);

            $types->each(function ($type) use ($key, $rules, &$content, &$fieldsRequired) {
                $params = $type->params;
                $type = $type->rule;

                $isRequired = $rules->contains('required');

                if ($isRequired)
                    $this->requiredFields($key, $content, $fieldsRequired);

                if (is_callable($type)) {
                    $typeFtn = $type($key, $params);
                    return Arr::set($content, $typeFtn->key, $typeFtn->type);
                }

                if ($type === 'array') {
                    Arr::set($content, $key, ['type' => 'array', 'items' => []]);
                    return;
                }

                if (Arr::has($content, $key) && Arr::get($content, "$key.type") === 'array') {
                    Arr::set($content, "$key.items.type", $type);
                    return;
                }

                Arr::set($content, $key, ['type' => $type]);
            });
        });


        return new BodyProperty((object)[
            'type' => 'object',
            'properties' => $content,
            'required' => $fieldsRequired,
        ]);
    }


    /**
     * Transform the rules to swagger format query
     * @param Collection $rules
     */
    public function transformToSwaggerQuery(Collection $rules): Collection
    {

        return  collect($rules)->transform(function ($rule, $key) {


            $rules = $this->collectRules($rule[$key]);

            $isRequired = $rules->contains('required');

            return new ParamProperty($key, $isRequired, 'query');
        });
    }


    /**
     * Set the required fields
     * @param string $key
     * @param array $content
     * @param array $fieldsRequired
     * @return void
     */
    public function requiredFields($key, &$content, &$fieldsRequired)
    {
        $keysArray = explode('.', $key);
        $keyValue = end($keysArray);
        $keys = str_replace("items.properties.$keyValue", 'items.required',  $key);
        $keys = str_replace("properties.$keyValue", 'required',  $keys);


        if (count($keysArray) == 1) {

            $fieldsRequired[] = $keyValue;
            return;
        }
        $subArray = Arr::get($content, $keys, []);

        $subArray[] = $keyValue;

        Arr::set($content, $keys, $subArray);
    }
}
