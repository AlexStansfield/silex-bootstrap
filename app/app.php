<?php

require_once __DIR__.'/bootstrap.php';

use Silex\Application;
use DerAlex\Silex\YamlConfigServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Symfony\Component\HttpFoundation\Response;

$app = new Application();

//-- Register Providers

// Configuration file
$env = getenv('APP_ENV') ?: 'dev';
$app->register(new YamlConfigServiceProvider(__DIR__ . '/config/' . $env . '.yml'));

// Set debug
$app['debug'] = $app['config']['debug'];

// Logging
$app->register(new MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/logs/' . $env . '.log',
));

// Sessions
$app->register(new SessionServiceProvider());

// Doctrine DBAL (database)
$app->register(new DoctrineServiceProvider(), array(
    'db.options' => $app['config']['doctrine']['dbal']
));

// Twig Templates
$app->register(new TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../src/Project/Views',
));

// Start the Session
$app['session']->start();

// Setup Routes
$app->get('/', 'Project\Controllers\IndexController::indexAction');

// Setup the error handling
$app->error(function (\Exception $e, $code) use ($app) {
    // If we're in debug mode than fall back to debug error handler
    if ($app['debug']) {
        return;
    }

    // Very simple messages for errors
    switch ($code) {
        case 404:
            $message = 'The requested page could not be found.';
            break;
        default:
            $message = 'We are sorry, but something went wrong. Please try again.';
    }

    return new Response($message);
});

// Run the App
$app->run();
