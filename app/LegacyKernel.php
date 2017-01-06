<?php

namespace Application;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class LegacyKernel implements HttpKernelInterface
{
    private $serviceContainer;
    private $applicationRoot;

    public function __construct($applicationRoot)
    {
        $this->applicationRoot = $applicationRoot;
        $this->serviceContainer = $this->getContainer();
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        return $this->serviceContainer->get('http_kernel')->handle($request);
    }

    private function getContainer()
    {
        $containerCache = $this->applicationRoot.'/cache/container.php';
        $containerClassName = 'ApplicationServiceContainer';
        if(!file_exists($containerCache)) {
            $container = $this->buildContainer();
            $dumper = new PhpDumper($container);

            file_put_contents($containerCache, $dumper->dump(['class' => $containerClassName]));
        } else {
            require $containerCache;
            $container = new $containerClassName();
        }

        return $container;
    }

    private function buildContainer()
    {
        $container = new ContainerBuilder();

        $container->setParameter('application.root', $this->applicationRoot);

        $locator = new FileLocator($this->applicationRoot.'/config');
        $serviceLoader = new YamlFileLoader($container, $locator);
        $serviceLoader->load('services.yml');

        return $container;
    }
}
