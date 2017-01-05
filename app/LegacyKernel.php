<?php

namespace Application;

use Application\Listener\ExceptionListener;
use Application\Listener\LegacyListener;
use Application\Listener\RouterListener;
use Application\Listener\TemplatePathInjectionListener;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

class LegacyKernel implements HttpKernelInterface
{
    private $applicationRoot;

    /**
     * @var HttpKernel
     */
    private $httpKernel;

    public function __construct($applicationRoot)
    {
        $this->applicationRoot = $applicationRoot;

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(
            KernelEvents::REQUEST,
            [new RouterListener($this->applicationRoot.'/config'), 'onKernelRequest'],
            16
        );

        $eventDispatcher->addListener(
            KernelEvents::REQUEST,
            [new LegacyListener($this->applicationRoot.'/legacy'), 'onKernelRequest'],
            8
        );

        $eventDispatcher->addListener(
            KernelEvents::CONTROLLER,
            [new TemplatePathInjectionListener($this->applicationRoot.'/views'), 'onKernelController']
        );

        $eventDispatcher->addListener(
            KernelEvents::EXCEPTION,
            [new ExceptionListener(), 'onKernelException']
        );

        $this->httpKernel = new HttpKernel(
            $eventDispatcher,
            new ControllerResolver()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        return $this->httpKernel->handle($request);

        /*
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
        */
    }

    private function renderScriptInResponse($script)
    {
        ob_start();
        $scriptPath = sprintf(
            '%s/legacy/%s.php',
            $this->applicationRoot,
            $script
        );

        if (!file_exists($scriptPath)) {
            throw new \RuntimeException('Can not find script.');
        }

        require $scriptPath;

        return new Response(ob_get_clean());
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
