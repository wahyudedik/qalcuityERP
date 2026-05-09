<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

// TEMPORARY DEBUG FILE - DELETE AFTER USE
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$request = Request::create(
    'http://qalcuityerp.test/login', 'GET', [], [], [],
    ['HTTP_HOST' => 'qalcuityerp.test', 'SERVER_NAME' => 'qalcuityerp.test']
);

// Patch: trace which middleware redirects
$originalHandle = null;
$middlewareTrace = [];

// Monkey-patch via event
$app->make('events')->listen('*', function ($event, $payload) use (&$middlewareTrace) {
    $middlewareTrace[] = $event;
});

$kernel = $app->make(Kernel::class);

// Use reflection to get middleware stack
$ref = new ReflectionClass($kernel);
$prop = $ref->getProperty('middleware');
$prop->setAccessible(true);
echo "GLOBAL MIDDLEWARE:\n";
foreach ($prop->getValue($kernel) as $m) {
    echo "  - $m\n";
}

try {
    $response = $kernel->handle($request);
    echo "\nSTATUS: ".$response->getStatusCode()."\n";
    echo 'LOCATION: '.($response->headers->get('Location') ?? 'NONE')."\n";
} catch (Throwable $e) {
    echo 'EXCEPTION: '.get_class($e).': '.$e->getMessage()."\n";
    echo 'AT: '.$e->getFile().':'.$e->getLine()."\n";
}
