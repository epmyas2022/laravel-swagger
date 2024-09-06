<?php

namespace App\Swagger\Trait;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait SwaggerConvertValidation
{

    public function getVarsList(): Collection
    {
        return collect([
            'decimal' => 'number',
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
            'file' => fn() => ['type' => 'string', 'format' => 'binary'],
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
            fn($rule) => $typesVars->keys()->contains($rule) ? $typesVars->get($rule) : null
        )->whereNotNull();
    }

    public function orderAsc(Collection $rules): Collection
    {
        return $rules->sortBy(function ($value, $key) {

            return $value;
        });
    }

    public function transformToSwagger(Collection $rules): object
    {


        $keyMain = $rules->keys()->first();
        $grouped = $this->orderAsc($rules->groupBy(fn() => $keyMain, true));

        $content = [];
        $grouped->each(function ($validation) use ($keyMain, &$content) {

            $validation->each(function ($property, $key) use ($keyMain, &$content) {

                $propertyCollection = $this->orderAsc($this->collectRules($property));

                $types = $this->getTypes($propertyCollection);
                $types->each(function ($type, $index) use ($keyMain, $key, &$content) {

                    if ($key != $keyMain) {
                        $keyUpdated = preg_replace("/.*\b{$keyMain}\b\.\*./", '', $key);

                        if (isset($content[$keyMain]['type']) && $content[$keyMain]['type']  == 'array') {

                            Arr::set($content, "{$keyMain}.items.type", $type);
                            return;
                        }
                        $content = Arr::add($content, "$keyMain.items.type", 'object');
                        $content = Arr::add($content, "$keyMain.items.properties.$keyUpdated.type", $type);
                        return;
                    }

                    if (isset($content[$keyMain]['type']) && $content[$keyMain]['type']  == 'array') {

                        Arr::set($content, "{$keyMain}.items.type", $type);
                        return;
                    }

                    Arr::set($content, "{$keyMain}.type", $type);
                });
            });
        });




        return (object) $content;
    }

    private function arrayFormat($property): array
    {


        return [
            'type' => 'array',
            'items' => [
                'type' => $property->type,
                'required' => $property->required
            ]
        ];
    }
}
