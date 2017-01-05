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
        $exception = $event->getException();
        $exceptionName = get_class($exception);
        switch ($exceptionName) {
            case ResourceNotFoundException::class:
                $response->setContent($exception->getMessage() ?: 'Resource not found ...');
                $response->setStatusCode(Response::HTTP_NOT_FOUND);
                break;
            default:
                var_dump($event->getException());
                $response->headers->add(['X-THROWN-EXCEPTION' => $exceptionName]);
                $response->headers->add(['X-THROWN-EXCEPTION-MESSAGE' => $event->getException()->getMessage()]);
        }
        $event->setResponse($response);
    }
}
