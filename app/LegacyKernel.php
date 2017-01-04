<?php

namespace Application;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

class LegacyKernel implements HttpKernelInterface
{
    private $pagesRoot;

    /**
     * @var HttpKernel
     */
    private $httpKernel;

    public function __construct($pagesRoot)
    {
        $this->pagesRoot = $pagesRoot;
        $this->httpKernel = new HttpKernel(
            new EventDispatcher(),
            new ControllerResolver()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        return $this->httpKernel->handle($request);

        $path = $request->getPathInfo();

        try {
            $routeParams = $this->routerMatchRequest($path);
            if (isset($routeParams['id'])) {
                $_GET['id'] = $routeParams['id'];
            }

            if (isset($routeParams['_script'])) {
                $response = $this->renderScriptInResponse($routeParams['_script']);
            } else {
                $request->attributes->add($routeParams);
                $response = $this->renderControllerInResponse($request);
            }
        } catch (ResourceNotFoundException $e) {
            $response = new Response('Page not found', Response::HTTP_NOT_FOUND);
        } catch (MethodNotAllowedException $e) {
            $response = new Response('Method not allowed', Response::HTTP_METHOD_NOT_ALLOWED);
        } catch (\RuntimeException $e) {
            throw new \Exception();
        } catch (\Exception $e) {
            var_dump($e);
            $response = new Response('Internal server error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $response;
    }

    private function renderScriptInResponse($script)
    {
        ob_start();
        $scriptPath = sprintf(
            '%s/%s.php',
            $this->pagesRoot,
            $script
        );

        if (!file_exists($scriptPath)) {
            throw new \RuntimeException('Can not find script.');
        }

        require $scriptPath;

        return new Response(ob_get_clean());
    }

    private function routerMatchRequest($path)
    {
        $fileLocator = new FileLocator($this->pagesRoot.'/config');
        $loader = new YamlFileLoader($fileLocator);
        $routeCollection = $loader->load('routing.yml');

        $requestContext = new RequestContext();

        $urlMatcher = new UrlMatcher($routeCollection, $requestContext);

        return $urlMatcher->match($path);
    }

    private function renderControllerInResponse(Request $request)
    {
        $controller = $request->attributes->get('_controller');

        if (!is_callable($controller)) {
            throw new \RuntimeException('The controller is not callable');
        }

        return new Response(call_user_func($controller, $request));
    }
}
