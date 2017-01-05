<?php

namespace Application;

use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Symfony\Component\Templating\TemplateNameParser;

class AbstractController
{
    private $templatesPath;

    /**
     * @var Router
     */
    private $router;

    /**
     * @param Router $router
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param mixed $templatesPath
     */
    public function setTemplatesPath($templatesPath)
    {
        $this->templatesPath = $templatesPath;
    }

    protected function render($templateName, $parameters)
    {
        $templateLoader = new \Twig_Loader_Filesystem($this->templatesPath);
        $templateNameParser = new TemplateNameParser();
        $twigEnvironment = new \Twig_Environment($templateLoader);

        $twigEnvironment->addFunction(new \Twig_SimpleFunction('generate_url', function($routeName, $routeParameters=[]) {
            return $this->router->generate($routeName, $routeParameters);
        }));

        $engine = new TwigEngine($twigEnvironment, $templateNameParser);

        return new Response($engine->render($templateName, $parameters));
    }

    protected function generateUrl($routeName, $routeParameters=[])
    {
        return $this->router->generate($routeName, $routeParameters);
    }

    protected function redirectToRoute($routeName, $routeParameters=[])
    {
        return new RedirectResponse($this->generateUrl($routeName, $routeParameters));
    }
}
