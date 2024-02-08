<?php

namespace SlothDevGuy\ProxyServices\Console;

use Illuminate\Console\Command;
use SlothDevGuy\ProxyServices\RouteStore;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Class ImportRoutes
 * @package SlothDevGuy\ProxyServices\Console
 */
#[AsCommand(name: 'proxy-services:import-routes')]
class ImportRoutes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proxy-services:import-routes {service} {--path=app/routes} {--from=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'import routes into a proxy-routes settings json file';

    public function handle(): int
    {
        $service = $this->argument('service');
        $routes = $this->getRoutes($this->option('from'));

        $builder = new RouteStore($service, $routes);
        $builder->store($this->option('path'));

        return static::SUCCESS;
    }

    /**
     * Returns the routes from a file
     *
     * @param string $from The file path to read the routes from.
     * @return array An array representing the decoded routes.
     */
    protected function getRoutes(string $from): array
    {
        $resource = fopen($from, 'r');

        $routes = json_decode(stream_get_contents($resource), true);

        fclose($resource);

        return $routes;
    }
}
