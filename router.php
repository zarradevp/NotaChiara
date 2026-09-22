<?php

declare(strict_types=1);

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');

if (str_starts_with($uri, '/data/')) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Accesso non consentito.';
    return true;
}

$file = __DIR__ . $uri;

if ($uri !== '/' && is_file($file)) {
    return false;
}

return false;
