<?php

namespace src\Swagger;

use src\Attributes\SwaggerGlobal;
use src\Attributes\SwaggerSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use src\Swagger\Trait\SwaggerConvertValidation;
use src\Swagger\Types\ParamProperty;
use src\Swagger\Types\RouteProperty;
use src\Attributes\SwaggerAuth;
use src\Attributes\SwaggerContent;
use src\Attributes\SwaggerResponse;
use src\Attributes\SwaggerSummary;
use src\Swagger\Trait\SwaggerDocument;

class SwaggerBuilder
{

    use SwaggerConvertValidation, SwaggerDocument;

    private $schema = [];

    private $attributesGlobal = [
        'security' => 'bearerAuth',
        'middleware' => 'auth',
    ];

    private  $typesResponse = [JsonResponse::class];


    public function initConfig()
    {
        $this->schema = config('swagger');

        return $this;
    }

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

            return 'src\\' . str_replace('/', '\\', $path) . $class;
        });
    }

    public function readSections(): self
    {

        $this->initConfig();

        $classes = $this->getClasses('Http/Controllers/');


        $classes->each(function ($class) {
            $reflection = new \ReflectionClass($class);

            $section = SwaggerAttribute::getAttribute(SwaggerSection::class, $reflection);
            $attributesGlobal = SwaggerAttribute::getAttribute(SwaggerGlobal::class, $reflection);

            if ($attributesGlobal)
                $this->setAttributesGlobal($attributesGlobal->attributes);

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

            if ($routeProperty->method == 'get') {
                $queryParams = $this->transformToSwaggerQuery($rules);

                return $queryParams->each(
                    fn($queryParam) => $this->setSchemaParameters($routeProperty, $queryParam)
                );
            }

            $body = $this->transformToSwagger($rules);

            $this->setComponent($renameClass, $body);
            $this->setSchemaBody($routeProperty, $renameClass, $content ?? 'srclication/json');
        });
    }



    public function getSchema($class, $methods, $tag = null): self
    {

        $methods->each(function ($method) use ($class, $tag) {

            $attributes = SwaggerAttribute::getAttributesMethod([
                SwaggerContent::class,
                SwaggerResponse::class,
                SwaggerSummary::class
            ], $method);

            $authAttribute = SwaggerAttribute::getAttribute(SwaggerAuth::class, $method);

            $route = $this->routes("$class@{$method->getName()}")->first();

            if (!$route) return;

            $uri = "/$route?->uri";
            $methodRoute = strtolower($route->methods[0]);

            $routeProperty = new RouteProperty([
                'id' => "/$route->uri.$methodRoute",
                'uri' => $uri,
                'method' => $methodRoute,

            ]);

            if ($this->isProtectedRoute($route))
                $this->setSchemaSecurity($routeProperty, [
                    $authAttribute->auth ?? $this->attributesGlobal['security'] => []
                ]);

            $this->parameters($method, $routeProperty, $attributes->contentBody ?? null);

            $this->setSchemaPath([$routeProperty->id, 'tags'], [$tag ?? 'default']);

            $this->setSchemaPath([$routeProperty->id, 'summary'], $attributes->summary ?? '');

            $this->setSchemaResponse($routeProperty, $attributes);
        });

        return $this;
    }



    /**
     * Determinar si una ruta es protegida
     * @param \Illuminate\Routing\Route $route
     * @return bool
     */
    public function isProtectedRoute($route)
    {
        $middleware = $route->getAction()['middleware'] ?? false;
        if (!$middleware) return false;

        return collect($middleware)->contains($this->attributesGlobal['middleware']);
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

    public function setAttributesGlobal($attributes)
    {
        collect($attributes)->each(function ($attribute, $key) {
            if (isset($this->attributesGlobal[$key]))
                $this->attributesGlobal[$key] = $attribute;
        });
        return $this;
    }

    public function build(): string
    {

        $json = json_encode($this->schema, JSON_PRETTY_PRINT);

        file_put_contents(public_path('swagger.json'), $json);

        return 'Swagger.json created';
    }
}