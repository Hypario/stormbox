<?php

use Framework\Exception\KnownExceptionResolver;

$resolver = $app->getContainer()->get(KnownExceptionResolver::class);

define('SERVER_ERROR', 500);
define('NOT_FOUND', 404);
define("FORBIDDEN", 403);
define('ERROR_REQUEST', 400);
define('UNKNOWN_ERROR', 520);


$resolver->register(SERVER_ERROR, "Internal Server Error");
$resolver->register(NOT_FOUND, "404 Not Found.");
$resolver->register(UNKNOWN_ERROR, "An unknown error happened, please contact the webmaster if it persist.");
