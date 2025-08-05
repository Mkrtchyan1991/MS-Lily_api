<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Route as RouteInstance;
use ReflectionMethod;
use Illuminate\Foundation\Http\FormRequest;

class GenerateApiDocs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docs:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate API documentation from routes, controllers, and request rules';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $routes = collect(Route::getRoutes())
            ->map(function (RouteInstance $route) {
                return [
                    'uri' => '/' . ltrim($route->uri(), '/'),
                    'methods' => $route->methods(),
                    'name' => $route->getName(),
                    'action' => $route->getActionName(),
                    'rules' => $this->getRules($route),
                ];
            });

        $path = public_path('api-docs.json');
        file_put_contents($path, $routes->values()->toJson(JSON_PRETTY_PRINT));

        $this->info("API documentation generated at {$path}");

        return self::SUCCESS;
    }

    /**
     * Extract validation rules from the route's controller.
     */
    protected function getRules(RouteInstance $route): array
    {
        $action = $route->getActionName();
        if (!str_contains($action, '@')) {
            return [];
        }

        [$controller, $method] = explode('@', $action);

        if (!class_exists($controller) || !method_exists($controller, $method)) {
            return [];
        }

        $reflection = new ReflectionMethod($controller, $method);
        $rules = [];

        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType();
            if ($type && is_subclass_of($type->getName(), FormRequest::class)) {
                $request = app($type->getName());
                $rules = array_merge($rules, $request->rules());
            }
        }

        return $rules;
    }
}
