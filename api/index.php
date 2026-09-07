<?php
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_uri = ltrim($request_uri, '/');

if ($request_uri === '' || $request_uri === '/') {
    $file = 'index.php';
} else {
    $file = $request_uri;
}

$path = __DIR__ . '/../' . $file;

if (file_exists($path) && is_file($path)) {
    if (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
        require $path;
    } else {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $mime_types = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
        ];
        if (isset($mime_types[$ext])) {
            header('Content-Type: ' . $mime_types[$ext]);
        }
        readfile($path);
    }
} elseif (file_exists($path . '.php')) {
    require $path . '.php';
} elseif (file_exists($path . '/index.php')) {
    require $path . '/index.php';
} else {
    http_response_code(404);
    echo "404 Not Found";
}
