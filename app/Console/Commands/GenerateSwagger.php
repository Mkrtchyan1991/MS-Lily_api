<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use ReflectionMethod;

class GenerateSwagger extends Command
{
    protected $signature = 'swagger:generate';

    protected $description = 'Generate OpenAPI specification from application routes';

    public function handle(): int
    {
        $routes = Route::getRoutes();
        $paths = [];

        foreach ($routes as $route) {
            $uri = $route->uri();
            if (!str_starts_with($uri, 'api')) {
                continue; // document only API routes
            }

            $uri = '/' . ltrim($uri, '/');
            $methods = array_diff($route->methods(), ['HEAD']);
            foreach ($methods as $method) {
                $methodLower = strtolower($method);
                $action = $route->getActionName();
                $summary = $action;
                $parameters = [];

                if (str_contains($action, '@')) {
                    [$controller, $controllerMethod] = explode('@', $action);
                    if (class_exists($controller) && method_exists($controller, $controllerMethod)) {
                        $reflection = new ReflectionMethod($controller, $controllerMethod);
                        $doc = $reflection->getDocComment();
                        if ($doc) {
                            $lines = preg_split('/\r?\n/', $doc);
                            $first = trim($lines[1] ?? '', " *\t\n\r\0\x0B");
                            if ($first !== '') {
                                $summary = $first;
                            }
                        }
                    }
                }

                foreach ($route->parameterNames() as $name) {
                    $parameters[] = [
                        'name' => $name,
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'string'],
                    ];
                }

                $paths[$uri][$methodLower] = [
                    'summary' => $summary,
                    'parameters' => $parameters,
                    'responses' => [
                        '200' => ['description' => 'Successful response'],
                    ],
                ];
            }
        }

        $openapi = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => config('app.name', 'Application') . ' API',
                'version' => '1.0.0',
                'description' => 'Automatically generated Swagger documentation',
            ],
            'paths' => $paths,
        ];

        $outputPath = public_path('swagger/openapi.json');
        if (!is_dir(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0755, true);
        }

        file_put_contents($outputPath, json_encode($openapi, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info("Swagger documentation generated at {$outputPath}");

        return self::SUCCESS;
    }
}
