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
use Symfony\Component\DependencyInjection\Reference;
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
        $serviceContainer = $this->registerServices();

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(
            KernelEvents::REQUEST,
            [new RouterListener($serviceContainer->get('yaml_file_loader')), 'onKernelRequest'],
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
            KernelEvents::CONTROLLER,
            [new RouterInjectionListener($serviceContainer->get('router')), 'onKernelController']
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

    private function registerServices()
    {
        $container = new ContainerBuilder();

        $fileLocatorDefinition = new Definition(FileLocator::class);
        $fileLocatorDefinition->addArgument($this->applicationRoot.'/config');

        $container->setDefinition('file_locator', $fileLocatorDefinition);

        $yamlFileLoaderDefinition = new Definition(YamlFileLoader::class);
        $yamlFileLoaderDefinition->addArgument(new Reference('file_locator'));

        $container->setDefinition('yaml_file_loader', $yamlFileLoaderDefinition);

        $routerDefinition = new Definition(Router::class);
        $routerDefinition->addArgument(new Reference('yaml_file_loader'));
        $routerDefinition->addArgument('routing.yml');

        $container->setDefinition('router', $routerDefinition);

        return $container;
    }
}
