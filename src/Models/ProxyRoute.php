<?php

namespace SlothDevGuy\ProxyServices\Models;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Class ProxyRoute
 * @package SlothDevGuy\ProxyServices\Models
 */
readonly class ProxyRoute implements Arrayable
{
    public function __construct(
        public string $name,
        public string $method,
        public string $uri,
        public array $permissions = [],
    )
    {

    }

    /**
     * Returns an array of HTTP methods associated with this route.
     *
     * @return array An array of HTTP methods (e.g., ['GET', 'POST'])
     */
    public function getMethods(): array
    {
        return explode('|', $this->method);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'method' => $this->method,
            'uri' => $this->uri,
            'permissions' => $this->permissions,
        ];
    }

    /**
     * @param array $route
     * @return static
     */
    public static function fromArray(array $route): static
    {
        $route = array_merge(static::defaults(), $route);

        return new static($route['name'], $route['method'], $route['uri'], $route['permissions']);
    }

    /**
     * @return array
     */
    public static function defaults(): array
    {
        return [
            'name' => null,
            'method' => null,
            'uri' => null,
            'permissions' => []
        ];
    }
}
