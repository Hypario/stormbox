<?php

$resolver = new \Hypario\KnownExceptionResolver();

define('ERROR_DEFAULT', -1);
define('ERROR_OK', 0);
define('ERROR_PATH', 1);
define('ERROR_UPLOAD_FAILED', 2);
define('ERROR_TOO_BIG_CHUNK', 3);
define('ERROR_MUST_BE_POSITIVE', 4);
define('ERROR_FILE_DONT_EXIST', 5);

$resolver->register(ERROR_DEFAULT, "Something wrong happened.");
$resolver->register(ERROR_OK, "Upload successful.");
$resolver->register(ERROR_PATH, "Your file must have a path (including the filename) to be uploaded.");
$resolver->register(ERROR_UPLOAD_FAILED, "Failed to upload file, please contact the support.");
$resolver->register(ERROR_TOO_BIG_CHUNK, "The chunk ID cannot be above the chunk count.");
$resolver->register(ERROR_MUST_BE_POSITIVE, "The chunk or the number of chunk must be positive.");
$resolver->register(ERROR_FILE_DONT_EXIST, "The wanted file or directory does not exist.");
