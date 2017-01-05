<?php

namespace Application;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Controller extends AbstractController
{
    public function homepageAction(Request $request)
    {
        $responseContent = $this->render('homepage.html.twig', [
            'name' => $request->get('name'),
        ]);

        return new Response($responseContent);
    }
}
