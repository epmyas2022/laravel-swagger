<?php

namespace App\Swagger;

use App\Attributes\SwaggerContent;
use App\Attributes\SwaggerResponse;
use App\Attributes\SwaggerSection as AttributesSwaggerSection;
use App\Attributes\SwaggerSummary;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use App\Swagger\SwaggerAttribute;
use App\Swagger\Trait\SwaggerConvertValidation;
use App\Swagger\Types\BodyProperty;
use App\Swagger\Types\ParamProperty;
use App\Swagger\Types\RouteProperty;

class SwaggerBuilder
{

    use SwaggerConvertValidation;

    private $schema = [
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'API',
            'version' => '1.0.0',
            'description' => 'API Documentation',
        ],
        'servers' => [
            ['url' => 'http://localhost:8002'],
        ],
        'paths' => [],

    ];

    private  $typesResponse = [JsonResponse::class];

    /**
     * Obtener clases de un directorio
     *  @param string $path
     * @return \Illuminate\Support\Collection
     */
    public function getClasses($path)
    {
        $classes = glob(app_path("$path*.php"));

        return collect($classes)->map(function ($class) use ($path) {

            $class = str_replace(app_path($path), '', $class);

            $class = str_replace('.php', '', $class);

            return 'App\\' . str_replace('/', '\\', $path) . $class;
        });
    }

    public function readSections(): self
    {

        $classes = $this->getClasses('Http/Controllers/');


        $classes->each(function ($class) {
            $reflection = new \ReflectionClass($class);

            $section = SwaggerAttribute::getAttribute(AttributesSwaggerSection::class, $reflection);


            if (!$section) return;

            $methods = collect($reflection->getMethods())->filter(function ($method) {
                return collect($this->typesResponse)->contains($method->getReturnType());
            });


            $this->getSchema($class, $methods, $section->description);
        });

        return $this;
    }





    public function parameters($method, RouteProperty $routeProperty, $content)
    {


        $parameters = $method->getParameters();

        collect($parameters)->each(function ($parameter) use ($routeProperty, $content) {
            $class =  $parameter?->getType()?->getName();


            if (!$class) return null;

            if (!class_exists($class) && $class)
                return $this->setSchemaParameters(
                    $routeProperty,
                    new ParamProperty($parameter->getName(), !$parameter->getType()->allowsNull(), 'path')
                );


            $reflection = new \ReflectionClass($class);

            $instance = $reflection->newInstance();

            $renameClass = explode('\\', $class);

            $renameClass = end($renameClass);




            $rules = collect($instance?->rules() ?? [])->groupBy(function ($rule, $key) {
                return strpos($key, '.') ? explode('.', $key)[0] : $key;
            }, true);

            $properties = $this->transformToSwagger($rules);

            $body = new BodyProperty($properties);

            $this->setComponent($renameClass, $body);
            $this->setSchemaBody($routeProperty, $renameClass, $content ?? 'application/json');


            /*     collect($rules)->each(function ($rule, $key) use ($routeProperty, $content, $class, &$requiredField) {


                $rules = $this->collectRules($rule[$key]);

                $isRequired = $rules->contains('required');

                if (
                    $routeProperty->method != 'post' &&
                    $routeProperty->method != 'put' &&
                    $routeProperty->method != 'patch'
                )
                    return $this->setSchemaParameters($routeProperty, new ParamProperty($key, $isRequired, 'query'));

                if ($isRequired) $requiredField[] = $key;

               
            }); */
        });
    }


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
        $this->setSchemaPath([$routeProperty->id, 'parameters'], [
            $parameter->toObject()
        ]);
    }

    public function setSchemaBody(RouteProperty $routeProperty, $class, $content)
    {

        $this->setSchemaPath(
            [$routeProperty->id, 'requestBody', 'content', $content, 'schema', '$ref'],
            "#/components/schemas/$class"
        );
    }

    public function getSchema($class, $methods, $tag = null): self
    {


        $methods->each(function ($method) use ($class, $tag) {

            $attributes = SwaggerAttribute::getAttributesMethod([
                SwaggerContent::class,
                SwaggerResponse::class,
                SwaggerSummary::class
            ], $method);

            $route = $this->routes("$class@{$method->getName()}")->first();

            if (!$route) return;

            $uri = "/$route?->uri";
            $methodRoute = strtolower($route->methods[0]);

            $routeProperty = new RouteProperty([
                'id' => "/$route->uri.$methodRoute",
                'uri' => $uri,
                'method' => $methodRoute,
            ]);

            $this->parameters($method, $routeProperty, $attributes->contentBody ?? null);

            $this->setSchemaPath([$routeProperty->id, 'tags'], [$tag ?? 'default']);

            $this->setSchemaPath([$routeProperty->id, 'summary'], $attributes->summary ?? '');

            $this->setSchemaResponse($routeProperty, $attributes);
        });

        return $this;
    }


    public function setSchemaResponse(RouteProperty $routeProperty, $attributes)
    {
        if (isset($attributes->status))
            $this->setSchemaPath([$routeProperty->id, 'responses'], [
                $attributes->status => [
                    'description' => $attributes->description,
                    'content' => $attributes->content
                ]
            ]);

        $this->setSchemaPath([$routeProperty->id, 'responses'], [
            '200' => ['description' => 'Success Response'],
            '400' => ['description' => 'Error in form',],
            '500' => ['description' => 'Internal Server Error',]
        ]);
    }



    /**
     * Obtener rutas de un controlador
     * @param string $class
     * @return \Illuminate\Support\Collection
     */
    public function routes($uses)
    {
        $routes = Route::getRoutes();

        return collect($routes->getRoutes())->filter(function ($route) use ($uses) {
            $controller = $route->getAction()['controller'] ?? null;
            return strpos($controller, $uses) !== false;
        });
    }

    public function build()
    {

        $json = json_encode($this->schema, JSON_PRETTY_PRINT);


        file_put_contents(public_path('swagger.json'), $json);

        return 'Swagger.json created';
    }
}
