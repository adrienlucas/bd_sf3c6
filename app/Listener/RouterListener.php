<?php

namespace Application\Listener;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

class RouterListener
{
    private $configurationPath;

    /**
     * @param $configurationPath
     */
    public function __construct($configurationPath)
    {
        $this->configurationPath = $configurationPath;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $routeParams = $this->routerMatchRequest($request->getPathInfo());

        $request->attributes->add($routeParams);
    }

    private function routerMatchRequest($path)
    {
        $fileLocator = new FileLocator($this->configurationPath);
        $loader = new YamlFileLoader($fileLocator);
        $routeCollection = $loader->load('routing.yml');

        $requestContext = new RequestContext();

        $urlMatcher = new UrlMatcher($routeCollection, $requestContext);

        return $urlMatcher->match($path);
    }
}