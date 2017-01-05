<?php

namespace Application;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Controller extends AbstractController
{
    public function homepageAction($name)
    {
        return $this->render('homepage.html.twig', [
            'name' => $name,
        ]);
    }
}
