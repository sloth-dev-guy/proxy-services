<?php

namespace SlothDevGuy\ProxyServices\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use SlothDevGuy\ProxyServices\Models\ProxyRoute;
use SlothDevGuy\ProxyServices\Models\ProxyService;

class ReverseProxyController extends Controller
{
    use AuthorizesRequests;

    /**
     * @param ProxyService $service
     * @param ProxyRoute $route
     * @return Response
     * @throws AuthorizationException
     */
    public function dispatch(ProxyService $service, ProxyRoute $route): Response
    {
        if (!empty($route->permissions)) {
            $this->authorize($route->permissions);
        }

        $http = Http::withOptions([
            'base_uri' => $service->base_uri,
            'debug' => app()->hasDebugModeEnabled(),
        ]);
        $http->acceptJson();

        $files = static::flattenFiles(request()->allFiles());
        foreach ($files as $key => $file)
            $http->attach($key, fopen($file->getRealPath(), 'r'), $file->getFilename());

//        if($user = $this->getUser()){
//            $http->withHeader('user', $user);
//        }

        $path = preg_replace("/^$service->name/", '', request()->path());
        logger()->info('reverse-proxy', [
            'service' => $service->toArray(),
            'path' => $path,
            'route' => $route->toArray(),
        ]);

        $response = match (strtolower(request()->method())){
            'head' => $http->head($path, request()->query()),
            'get' => $http->get($path, request()->query()),
            'post' => $http->post($path, request()->input()),
            'put' => $http->put($path, request()->input()),
            'patch' => $http->patch($path, request()->input()),
            'delete' => $http->delete($path, request()->input()),
        };

        return response($response->body(), $response->status());
    }

    /**
     * @param array $files
     * @param mixed|null $parentKey
     * @return array|UploadedFile[]
     */
    public static function flattenFiles(array $files, mixed $parentKey = null): array
    {
        $flatten = [];

        foreach ($files as $index => $file) {
            $key = is_null($parentKey)? $index : "{$parentKey}[$index]";

            if(is_array($file)){
                $flatten = array_merge($flatten, static::flattenFiles($file, $key));
            }
            else{
                $flatten[$key] = $file instanceof UploadedFile? $file : null;
            }
        }

        return array_filter($flatten);
    }

    public function getUser(): string|null
    {
        return '';
    }
}
