<?php

namespace SlothDevGuy\ProxyServices;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use SlothDevGuy\ProxyServices\Models\ProxyRoute;
use SlothDevGuy\ProxyServices\Models\ProxyService;

class RouteStore
{
    /**
     * @var array
     */
    protected array $serviceOptions;

    public function __construct(
        protected readonly string $service,
        protected readonly array  $newRoutes,
        protected readonly bool   $sync = true,
    )
    {

    }

    public function store(string $path): void
    {
        $path = $this->loadServiceOptions($path);
        $this->setServer();
        $this->setRoutes();

        file_put_contents($path, json_encode($this->serviceOptions, JSON_PRETTY_PRINT));

        logger()->info('service routes stored', [
            'server' => $this->serviceOptions['server'],
            'routes' => count($this->serviceOptions['routes']),
        ]);
    }

    public function setServer(): void
    {
        $server = ProxyService::values($this->service, data_get($this->serviceOptions, 'server.middlewares', []));

        data_set($this->serviceOptions, 'server', $server);
    }

    public function setRoutes(): void
    {
        $routes = collect($this->newRoutes)
            ->filter(fn(array $route) => (bool) $route['name'])
            ->mapWithKeys(fn(array $route) => [
                $route['name'] => $this->buildRoute($route)
            ])
            ->toArray();

        $routes = $this->sync? $routes : array_merge(data_get($this->serviceOptions, 'routes', []), $routes);

        data_set($this->serviceOptions, 'routes', $routes);
    }

    public function buildRoute(array $route): array
    {
        $currentRoute = data_get($this->serviceOptions, "routes.{$route['name']}", []);

        return ProxyRoute::fromArray(array_merge($currentRoute, $route))->toArray();
    }

    public function loadServiceOptions(string $path): string
    {
        $path = storage_path($path);
        file_exists($path) || mkdir($path, 0755, true);
        $path .= DIRECTORY_SEPARATOR . "$this->service.json";

        logger()->info('decoding service routes', compact('path'));
        $this->serviceOptions = json_decode(@file_get_contents($path), true) ?? [];

        return $path;
    }
}
