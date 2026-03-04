<?php
/**
 * Router for PHP built-in server: serve static files and route /api/* to api/index.php.
 * Run: php -S localhost:8000 router.php
 */
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (preg_match('#^/api(?:/(.*))?$#', $uri, $m)) {
    $_GET['path'] = $m[1] ?? '';
    require __DIR__ . '/api/index.php';
    return true;
}

if ($uri === '/' || $uri === '') {
    $uri = '/index.html';
}

$file = __DIR__ . $uri;
if (is_file($file) && pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
    return false; // let built-in server serve the file
}

if (is_file(__DIR__ . $uri . '.html')) {
    return false;
}

// 404
http_response_code(404);
echo '404 Not Found';
return true;
