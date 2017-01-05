<?php

namespace Application\Listener;


use Application\AbstractController;
use Application\DAO\TodoDAO;
use Application\TodoDAOAwareInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class TodoDAOInjectionListener
{
    private $dao;

    public function __construct(TodoDAO $dao)
    {
        $this->dao = $dao;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        if (!is_array($controller) || !is_object($controller[0]) || !$controller[0] instanceof TodoDAOAwareInterface) {
            return;
        }

        $controller[0]->setTodoDAO($this->dao);
    }
}