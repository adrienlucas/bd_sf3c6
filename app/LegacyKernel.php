<?php

namespace Application;

use Application\DAO\TodoDAO;
use Application\Listener\ExceptionListener;
use Application\Listener\LegacyListener;
use Application\Listener\RouterInjectionListener;
use Application\Listener\RouterListener;
use Application\Listener\TemplatePathInjectionListener;
use Application\Listener\TodoDAOInjectionListener;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Router;

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


        $fileLocator = new FileLocator($this->applicationRoot.'/config');
        $loader = new YamlFileLoader($fileLocator);
        $router = new Router($loader, 'routing.yml');

        $eventDispatcher->addListener(
            KernelEvents::CONTROLLER,
            [new RouterInjectionListener($router), 'onKernelController']
        );

        $todoDAO = new TodoDAO();
        $eventDispatcher->addListener(
            KernelEvents::CONTROLLER,
            [new TodoDAOInjectionListener($todoDAO), 'onKernelController']
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
    }
}
