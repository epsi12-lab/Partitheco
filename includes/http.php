<?php
// includes/http.php

function json_response($data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function json_error(string $message, int $statusCode = 400, array $extra = []): void {
    json_response(array_merge(['error' => $message], $extra), $statusCode);
}

function json_success(array $payload = [], int $statusCode = 200): void {
    json_response(array_merge(['success' => true], $payload), $statusCode);
}

function download_response(string $content, string $contentType, string $filename): void {
    http_response_code(200);
    header('Content-Type: ' . $contentType);
    header('Content-Disposition: attachment; filename="' . safe_download_filename($filename) . '"');
    echo $content;
    exit;
}

function file_download_response(string $filePath, string $filename, ?string $contentType = null): void {
    http_response_code(200);
    header('Content-Type: ' . ($contentType ?: mime_content_type($filePath) ?: 'application/octet-stream'));
    header('Content-Disposition: attachment; filename="' . safe_download_filename($filename) . '"');
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
    exit;
}

function safe_download_filename(string $filename): string {
    $sanitized = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename);
    return $sanitized !== '' ? $sanitized : 'download';
}
