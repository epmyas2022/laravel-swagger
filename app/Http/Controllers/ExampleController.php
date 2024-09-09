<?php

namespace App\Http\Controllers;

use App\Attributes\SwaggerContent;
use App\Attributes\SwaggerResponse;
use App\Http\Requests\ExampleRequest;
use Illuminate\Http\JsonResponse;
use App\Attributes\SwaggerSection;
use App\Attributes\SwaggerSummary;

#[SwaggerSection('Example Test')]
class ExampleController extends Controller
{


    #[SwaggerSummary('Example Test')]
    public function index(string $id): JsonResponse
    {
        return response()->json(['message' => 'Hello World']);
    }

    #[SwaggerSummary('Example Test Store')]

    public function store(ExampleRequest $request): JsonResponse
    {
        return response()->json(['message' => 'Hello World']);
    }

    #[SwaggerSummary('Example Test Update')]
    public function update(ExampleRequest $request, string $id): JsonResponse
    {
        return response()->json(['message' => 'Hello World']);
    }

    #[SwaggerSummary('Example Test Destroy')]
    public function destroy(string $id): JsonResponse
    {
        return response()->json(['message' => 'Hello World']);
    }
}
