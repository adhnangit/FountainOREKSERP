<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// cPanel/Apache strips the Authorization header on many shared hosts.
// Recover it from the environment variable set by the .htaccess RewriteRule,
// or from apache_request_headers() as a last resort.
if (empty($_SERVER['HTTP_AUTHORIZATION'])) {
    if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $_SERVER['HTTP_AUTHORIZATION'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    } elseif (function_exists('apache_request_headers')) {
        $allHeaders = array_change_key_case(apache_request_headers(), CASE_LOWER);
        if (!empty($allHeaders['authorization'])) {
            $_SERVER['HTTP_AUTHORIZATION'] = $allHeaders['authorization'];
        }
    }
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Apache's threaded MPM on Windows shares one process across many concurrent
// requests (and, on this host, many different XAMPP-hosted projects). Laravel's
// default env loader calls putenv(), which mutates that shared, process-wide
// environment table — so one request's .env values can leak into a concurrent
// request on a sibling thread, intermittently serving this app with another
// project's config (seen in practice: sporadic 500s/401s from a request
// resolving against a stale/wrong environment). Disabling putenv makes Laravel
// read env vars from the request-local $_ENV/$_SERVER superglobals instead,
// which are thread-safe.
\Illuminate\Support\Env::disablePutenv();

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
