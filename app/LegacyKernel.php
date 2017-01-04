<?php

namespace Application;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class LegacyKernel implements HttpKernelInterface
{
    private $pagesRoot;

    public function __construct($pagesRoot)
    {
        $this->pagesRoot = $pagesRoot;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $path = $request->getPathInfo();

        try{
            $routeParams = $this->routerMatchRequest($path);
            if(isset($routeParams['id'])) {
                $_GET['id'] = $routeParams['id'];
            }
            $response = $this->renderInResponse($routeParams['_script']);
        } catch (ResourceNotFoundException $e) {
            $response = new Response('Page not found', Response::HTTP_NOT_FOUND);
        } catch (MethodNotAllowedException $e) {
            $response = new Response('Method not allowed', Response::HTTP_METHOD_NOT_ALLOWED);
        } catch (\Exception $e) {
            $response = new Response('Internal server error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $response;
    }


    private function renderInResponse($script)
    {
        ob_start();
        $scriptPath = sprintf(
            '%s/%s.php',
            $this->pagesRoot,
            $script
        );

        require $scriptPath;

        return new Response(ob_get_clean());
    }

    private function routerMatchRequest($path)
    {
        $routeCollection = new RouteCollection();

        $routeCollection->add('app_homepage', new Route('/', ['_script' => 'list']));
        $routeCollection->add('app_list', new Route('/list', ['_script' => 'list']));
        $routeCollection->add('app_todo', new Route(
            '/todo/{id}',
            ['_script' => 'todo'],
            ['id' => '\d+']
        ));

        $requestContext = new RequestContext();

        $urlMatcher = new UrlMatcher($routeCollection, $requestContext);

        return $urlMatcher->match($path);
    }
}