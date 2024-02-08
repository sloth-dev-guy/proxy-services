<?php

namespace SlothDevGuy\ProxyServices;

use Illuminate\Support\Facades\Route;
use SlothDevGuy\ProxyServices\Http\Controllers\ReverseProxyController;
use SlothDevGuy\ProxyServices\Models\ProxyRoute;
use SlothDevGuy\ProxyServices\Models\ProxyService;

class RouteRegister
{
    protected ReverseProxyController $controller;

    public function __construct(
        protected string $path = 'app/routes',
    )
    {
        $this->controller = app(ReverseProxyController::class);
    }

    public function registerRoutes(array $middlewares = []): void
    {
        $path = storage_path($this->path);
        $services = glob($path . DIRECTORY_SEPARATOR . '*.json');

        foreach ($services as $service) {
            $settings = json_decode(file_get_contents($service), true);

            $service = ProxyService::fromArray(data_get($settings, 'server'));
            $routes = array_map(fn(array $route) => ProxyRoute::fromArray($route), data_get($settings, 'routes', []));

            Route::prefix($service->name)
                ->as("$service->name.")
                ->middleware($service->getMiddlewares($middlewares))
                ->group(fn() =>
                    array_map(
                        fn(ProxyRoute $route) => Route::match(
                            $route->getMethods(),
                            $route->uri,
                            fn() => $this->controller->dispatch($service, $route)
                        )->name($route->name),
                        $routes
                    )
                );
        }
    }
}
