<?php

use Carbon\Carbon;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\HttpCacheServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

date_default_timezone_set('Europe/Rome');

define('ROOT_PATH', __DIR__.'/..');

//handling CORS preflight request
$app->before(function (Request $request) {
   if ($request->getMethod() === 'OPTIONS') {
       $response = new Response();
       $response->headers->set('Access-Control-Allow-Origin', '*');
       $response->headers->set('Access-Control-Allow-Methods', 'GET,POST,PUT,DELETE,OPTIONS');
       $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');
       $response->setStatusCode(200);
       $response->send();
   }
}, Application::EARLY_EVENT);

//handling CORS respons with right headers
$app->after(function (Request $request, Response $response) {
   $response->headers->set('Access-Control-Allow-Origin', '*');
   $response->headers->set('Access-Control-Allow-Methods', 'GET,POST,PUT,DELETE,OPTIONS');
});

//accepting JSON
$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : []);
    }
});

$app->register(new ServiceControllerServiceProvider());

$app->register(new DoctrineServiceProvider(), [
    'db.options' => [
        'driver' => 'pdo_sqlite',
        'path'   => realpath(ROOT_PATH.'/app.db'),
    ],
]);

$app->register(new HttpCacheServiceProvider(), ['http_cache.cache_dir' => ROOT_PATH.'/storage/cache']);

$app->register(new MonologServiceProvider(), [
    'monolog.logfile' => ROOT_PATH.'/storage/logs/'.Carbon::now('Europe/Rome')->format('Y-m-d').'.log',
    'monolog.level'   => $app['log.level'],
    'monolog.name'    => 'application',
]);

//load services
$servicesLoader = new App\ServicesLoader($app);
$servicesLoader->bindServicesIntoContainer();

//load routes
$routesLoader = new App\RoutesLoader($app);
$routesLoader->bindRoutesToControllers();

$app->error(function (\Exception $e, $code) use ($app) {
    $app['monolog']->addError($e->getMessage());
    $app['monolog']->addError($e->getTraceAsString());

    return new JsonResponse(['statusCode' => $code, 'message' => $e->getMessage(), 'stacktrace' => $e->getTraceAsString()]);
});

return $app;
