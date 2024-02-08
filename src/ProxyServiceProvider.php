<?php

namespace SlothDevGuy\ProxyServices;

use Illuminate\Support\ServiceProvider;
use SlothDevGuy\ProxyServices\Console\ImportRoutes;

/**
 * Class ProxyServiceProvider
 * @package SlothDevGuy\ProxyServices
 */
class ProxyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function boot(array $middlewares = []): void
    {
        $this->registerCommands();
    }

    /**
     * Register the commands.
     *
     * @return void
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ImportRoutes::class,
            ]);
        }
    }
}
