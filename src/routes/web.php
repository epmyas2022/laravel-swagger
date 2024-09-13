<?php

use Laravel\Swagger\SwaggerBuilder;
use Illuminate\Support\Facades\Route;




Route::get('/docs', function () {
    $builder = new SwaggerBuilder();

    $builder->readSections()->build();
    return view('swagger.index');
});
