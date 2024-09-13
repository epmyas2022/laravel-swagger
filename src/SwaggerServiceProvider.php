<?php
namespace Laravel\Swagger;
use Illuminate\Support\ServiceProvider;

class SwaggerServiceProvider extends ServiceProvider{
    public function register()
    {
        // Registrar servicios


        $this->publishes([
            __DIR__.'/config/swagger.php' => config_path('swagger.php')
        ]);
    }

    public function boot()
    {
        // Publicar archivos, como vistas o configuraciones, si es necesario

        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadViewsFrom(__DIR__.'./resources/views', 'swagger');
    
    }
}