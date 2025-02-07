<?php

use Laravel\Swagger\Document\SwaggerBuilder;
use Illuminate\Support\Facades\Route;


Route::get('/docs', function () {
    return view('swagger::index', [
        'theme' => config('swagger.options.theme')
    ]);
});

Route::get('/api-docs', function () {
    $builder = new SwaggerBuilder();

    $builder->readSections();

    return  $builder->responseJson();
});
