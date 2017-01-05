<?php

namespace Application\Listener;

use Application\AbstractController;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class TemplatePathInjectionListener
{
    private $templatesPath;

    public function __construct($templatesPath)
    {
        $this->templatesPath = $templatesPath;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        if (!is_array($controller) || !isset($controller[0]) || !$controller[0] instanceof AbstractController) {
            return;
        }

        $controller[0]->setTemplatesPath($this->templatesPath);
    }
}
