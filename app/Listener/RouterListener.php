<?php

namespace Application\Listener;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

class RouterListener
{
    private $loader;

    public function __construct($loader)
    {
        $this->loader = $loader;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $routeParams = $this->routerMatchRequest($request->getPathInfo());

        $request->attributes->add($routeParams);
    }

    private function routerMatchRequest($path)
    {
        $routeCollection = $this->loader->load('routing.yml');

        $requestContext = new RequestContext();

        $urlMatcher = new UrlMatcher($routeCollection, $requestContext);

        return $urlMatcher->match($path);
    }
}
