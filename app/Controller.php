<?php

namespace Application;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Controller
{
    public static function homepageAction(Request $request)
    {
        $responseContent = sprintf(
            'Welcome %s on the todo list !', $request->get('name')
        );

        return new Response($responseContent);
    }
}
