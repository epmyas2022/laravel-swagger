<?php
namespace Laravel\Swagger;
use Illuminate\Support\ServiceProvider;

class SwaggerServiceProvider extends ServiceProvider{
    public function register()
    {
     
        $this->publishes([
            __DIR__.'/config/swagger.php' => config_path('swagger.php')
        ]);

        //assets 

        $this->publishes([
            __DIR__. '/public/css' => public_path('swagger/css'),
            __DIR__. '/public/js' => public_path('swagger/js'),
            __DIR__. '/public/icons' => public_path('swagger/icons'),
        ]);
    }

    public function boot()
    {
        // Publicar archivos, como vistas o configuraciones, si es necesario

        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadViewsFrom(__DIR__.'/resources/views/swagger', 'swagger');
    
    }
}