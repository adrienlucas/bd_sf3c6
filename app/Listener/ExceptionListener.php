<?php

namespace Application\Listener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class ExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $response = new Response('Internal server error', Response::HTTP_INTERNAL_SERVER_ERROR);
        $exceptionName = get_class($event->getException());
        switch ($exceptionName) {
            case ResourceNotFoundException::class:
                $response->setContent('Page not found ...');
                $response->setStatusCode(Response::HTTP_NOT_FOUND);
                break;
            default:
                $response->headers->add(['X-THROWN-EXCEPTION' => $exceptionName]);
                $response->headers->add(['X-THROWN-EXCEPTION-MESSAGE' => $event->getException()->getMessage()]);
        }
        $event->setResponse($response);
    }
}
