<?php

namespace Application;

use Application\Listener\DatabaseConnectionInjectionListener;
use Application\Listener\ExceptionListener;
use Application\Listener\LegacyListener;
use Application\Listener\RouterListener;
use Application\Listener\TemplatePathInjectionListener;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

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

        $connection = $this->connectToDatabase();
        $eventDispatcher->addListener(
            KernelEvents::CONTROLLER,
            [new DatabaseConnectionInjectionListener($connection), 'onKernelController']
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

    private function connectToDatabase()
    {
        if (!$conn = mysqli_connect('localhost', 'root', 'toor')) {
            die('Unable to connect to MySQL : '.mysql_errno().' '.mysql_error());
        }

        mysqli_select_db($conn, 'training_todo') or die('Unable to select database "training_todo"');

        return $conn;
    }
}
