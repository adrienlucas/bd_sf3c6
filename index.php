<?php

error_reporting(E_ALL);

require __DIR__.'/vendor/autoload.php';

use \Application\LegacyKernel;
use \Symfony\Component\HttpFoundation\Request;

$kernel = new LegacyKernel(__DIR__.'/app');

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();