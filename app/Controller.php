<?php

namespace Application;

use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Templating\TemplateNameParser;

class Controller extends AbstractController
{
    public function homepageAction(Request $request)
    {
        $responseContent = $this->render('homepage.html.twig', [
            'name' => $request->get('name')
        ]);

        return new Response($responseContent);
    }
}
