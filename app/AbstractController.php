<?php

namespace Application;


use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\Templating\TemplateNameParser;

class AbstractController
{
    private $templatesPath;

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

        $engine = new TwigEngine($twigEnvironment, $templateNameParser);
        return $engine->render($templateName, $parameters);
    }
}