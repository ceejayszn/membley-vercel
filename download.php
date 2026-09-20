<?php
/**
 * Force Direct File Download Script for Mobile and Desktop Devices.
 * Forces the browser to save files (images, PDFs, documents) directly to local storage.
 */

$file_url = trim($_GET['url'] ?? $_GET['file'] ?? '');

if (empty($file_url)) {
    http_response_code(400);
    die("Missing file URL.");
}

// Sanitize & validate URL (allow HTTP/HTTPS or local assets)
if (strpos($file_url, 'http://') !== 0 && strpos($file_url, 'https://') !== 0) {
    // Relative local file
    $file_url = ltrim($file_url, '/');
    $local_path = __DIR__ . '/' . $file_url;
    if (!file_exists($local_path)) {
        http_response_code(404);
        die("File not found.");
    }
    $filename = basename($local_path);
    $mime_type = mime_content_type($local_path) ?: 'application/octet-stream';
    $file_size = filesize($local_path);
    
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . $file_size);
    readfile($local_path);
    exit;
}

// Remote URL (e.g. Vercel Blob)
$parsed = parse_url($file_url);
$filename = basename($parsed['path'] ?? 'downloaded_file');
if (empty(pathinfo($filename, PATHINFO_EXTENSION))) {
    $filename .= '.bin';
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $file_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$file_data = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE) ?: 'application/octet-stream';
curl_close($ch);

if ($http_code >= 200 && $http_code < 300 && $file_data !== false) {
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $content_type);
    header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . strlen($file_data));
    echo $file_data;
    exit;
}

// Fallback redirect if download proxy fails
header("Location: " . $file_url);
exit;
