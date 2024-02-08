<?php

namespace SlothDevGuy\ProxyServices\Models;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

/**
 * Class ProxyService
 * @package SlothDevGuy\ProxyServices\Models
 */
readonly class ProxyService implements Arrayable
{
    public function __construct(
        public string $name,
        public string $base_uri,
        public array $middlewares = [],
    )
    {

    }

    /**
     * Get the list of middlewares.
     *
     * This method retrieves the list of middlewares to be applied. The method
     * takes an optional input parameter $middlewares, which represents additional
     * middlewares to be merged. The resulting array is obtained by merging the
     * default 'web' middleware, the input $middlewares, and the class property
     * $this->middlewares.
     *
     * @param array $middlewares Optional array of middlewares to be merged.
     * @return array The array of middlewares after merging and filtering out duplicates.
     */
    public function getMiddlewares(array $middlewares = []): array
    {
        $middlewares = array_merge(['web'], $middlewares, $this->middlewares);

        return array_filter(array_unique($middlewares));
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'base_uri' => $this->base_uri,
            'middlewares' => $this->middlewares,
        ];
    }

    /**
     * @param array $service
     * @return static
     */
    public static function fromArray(array $service): static
    {
        return new static(
            $service['name'],
            env($service['base_uri'], $service['base_uri']),
            data_get($service, 'middlewares', [])
        );
    }

    /**
     * @param string $service
     * @param array $middlewares
     * @return array
     */
    public static function values(string $service, array $middlewares = []): array
    {
        $baseUri = Str::replace(['-'], ['_'], strtoupper($service)) . '_URI';

        return [
            'name' => $service,
            'base_uri' => $baseUri,
            'middlewares' => $middlewares,
        ];
    }
}
