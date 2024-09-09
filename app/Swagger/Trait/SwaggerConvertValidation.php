<?php

namespace App\Swagger\Trait;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait SwaggerConvertValidation
{

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
                $key = str_replace('items.properties', 'items',  $key);
                return (object)[
                    'key' => $key,
                    'type' => [
                        'type' => 'string',
                        'format' => 'binary',
                    ]
                ];
            },
            'in:' => function ($key, $params) {
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
     * Convert the rules to a collection
     * @param array|string $rules
     * @return Collection
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
                $params = null;

                if (Str::startsWith($rule, 'in:')) {
                    $params =  explode(',', Str::after($rule, 'in:'));
                    $rule = 'in:';
                }

                return $typesVars->keys()->contains($rule) ? (object)
                [
                    'params' => $params,
                    'rule' => $typesVars->get($rule)
                ] : null;
            }
        )->whereNotNull();
    }




    public function orderAsc(Collection $rules): Collection
    {
        return $rules->sortBy(function ($value) {

            return $value;
        });
    }


    public function mapperKey($key)
    {
        if (!strpos($key, '*') && strpos($key, '.'))
            return str_replace('.', '.properties.',  $key);

        return str_replace('*', 'items.properties',  $key);
    }

    public function transformToSwagger(Collection $rules): object
    {

        $grouped = $this->orderAsc($rules->collapse());

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

                if (is_callable($type)) {
                    $typeFtn = $type($key, $params);
                    return Arr::set($content, $typeFtn->key, $typeFtn->type);
                }

                if ($type === 'array') {
                    Arr::set($content, $key, ['type' => 'array', 'items' => []]);
                    return;
                }

                if (isset($content[$key]) && $content[$key]['type'] === 'array') {
                    Arr::set($content, "$key.items.type", $type);
                    return;
                }

                if ($isRequired) {
                    $this->requiredFields($key, $content, $fieldsRequired);
                }


                Arr::set($content, $key, [
                    'type' => $type,

                ]);
            });
        });



        return (object) [
            'type' => 'object',
            'properties' => $content,
            'required' => $fieldsRequired,
        ];
    }


    public function requiredFields($key, &$content, &$fieldsRequired)
    {

        $keysArray = explode('.', $key);
        $keyValue = end($keysArray);
        $keys = str_replace("items.properties.$keyValue", 'items.required',  $key);

        if (count($keysArray) == 1) {
            $fieldsRequired[] = $keyValue;
            return;
        }
        $subArray = Arr::get($content, $keys, []);

        $subArray[] = $keyValue;

        Arr::set($content, $keys, $subArray);
    }
}
