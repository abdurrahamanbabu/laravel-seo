<?php

declare(strict_types=1);

namespace Abdurrahaman\LaravelSeo\Http\Controllers;

use Abdurrahaman\LaravelSeo\Http\Requests\GenerateSeoRequest;
use Abdurrahaman\LaravelSeo\Services\AiSeoGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Throwable;

class GenerateSeoController extends Controller
{
    public function __invoke(GenerateSeoRequest $request, AiSeoGenerator $generator): JsonResponse
    {
        if (! config('laravel-seo.ai.enabled')) {
            return response()->json([
                'message' => 'AI SEO generation is disabled.',
            ], 403);
        }

        try {
            $result = $generator->generate(
                content: (string) $request->input('content'),
                currentTitle: $request->input('title'),
                language: $request->input('language'),
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
