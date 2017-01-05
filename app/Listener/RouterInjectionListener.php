<?php

namespace Application\Listener;

use Application\AbstractController;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Routing\Router;

class RouterInjectionListener
{
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        if (!is_array($controller) || !is_object($controller[0]) || !$controller[0] instanceof AbstractController) {
            return;
        }

        $controller[0]->setRouter($this->router);
    }
}
