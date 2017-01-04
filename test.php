<?php

namespace Application;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require __DIR__.'/vendor/autoload.php';

// My controller
function myCountPage(Request $request)
{
    $count = $request->cookies->get('app_count', 0);

    $response = new Response(
        sprintf('Vous êtes venus %d fois !', $count)
    );

    $response->headers->setCookie(
        new Cookie('app_count', ++$count)
    );

    return $response;
}


$request = Request::createFromGlobals();
$response = myCountPage($request);
$response->send();