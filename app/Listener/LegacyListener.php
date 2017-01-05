<?php

namespace Application\Listener;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class LegacyListener
{

    private $legacyRoot;

    /**
     * @param $legacyRoot
     */
    public function __construct($legacyRoot)
    {
        $this->legacyRoot = $legacyRoot;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $requestAttributes = $event->getRequest()->attributes;
        if(!$requestAttributes->has('_script')) {
            return;
        }

        $response = $this->renderScriptInResponse($requestAttributes->get('_script'));
        $event->setResponse($response);
    }

    private function renderScriptInResponse($script)
    {
        ob_start();
        $scriptPath = sprintf(
            '%s/%s.php',
            $this->legacyRoot,
            $script
        );

        if (!file_exists($scriptPath)) {
            throw new \RuntimeException('Can not find script.');
        }

        require $scriptPath;

        return new Response(ob_get_clean());
    }
}