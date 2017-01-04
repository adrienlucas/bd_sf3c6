<?php

namespace Application;

class Controller
{
    public static function homepageAction($name)
    {
        return sprintf(
            'Welcome %s on the todo list !', $name
        );
    }
}
