<?php

namespace Application;

use Symfony\Component\HttpFoundation\Request;

class Controller
{
    static public function homepageAction($name){
        return sprintf(
            'Welcome %s on the todo list !', $name
        );
    }
}