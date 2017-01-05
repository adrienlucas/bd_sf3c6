<?php

namespace Application\Listener;


use Application\AbstractController;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class DatabaseConnectionInjectionListener
{
    private $connection;

    /**
     * DatabaseConnectionInjectionListener constructor.
     * @param \mysqli $connection
     */
    public function __construct($connection)
    {
        $this->connection = $connection;
    }


    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        if (!is_array($controller) || !is_object($controller[0]) || !$controller[0] instanceof AbstractController) {
            return;
        }

        $controller[0]->setDatabaseConnection($this->connection);
    }
}