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
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Loader\YamlFileLoader as RoutingYamlFileLoader;
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
        $serviceContainer = $this->registerServices();

        $eventDispatcher = new EventDispatcher();

        $eventDispatcher->addListener(
            KernelEvents::REQUEST,
            [new RouterListener($serviceContainer->get('routing_file_loader')), 'onKernelRequest'],
            16
        );

        $eventDispatcher->addListener(
            KernelEvents::CONTROLLER,
            [$serviceContainer->get('app.listener.template_path_injection'), 'onKernelController']
        );


        $eventDispatcher->addListener(
            KernelEvents::CONTROLLER,
            [$serviceContainer->get('app.listener.router_injection'), 'onKernelController']
        );

        $todoDAO = new TodoDAO();
        $eventDispatcher->addListener(
            KernelEvents::CONTROLLER,
            [new TodoDAOInjectionListener($todoDAO), 'onKernelController']
        );

        $eventDispatcher->addListener(
            KernelEvents::EXCEPTION,
            [$serviceContainer->get('app.listener.exception'), 'onKernelException']
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

    private function registerServices()
    {
        $container = new ContainerBuilder();

        $container->setParameter('application.root', $this->applicationRoot);

        $locator = new FileLocator($this->applicationRoot.'/config');
        $serviceLoader = new YamlFileLoader($container,$locator);
        $serviceLoader->load('services.yml');

        return $container;
    }
}
