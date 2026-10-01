<?php

function env(string $key, $default = null) {
    $value = getenv($key);

    if ($value === false || $value === null || $value === '') {
        return $default;
    }

    return trim($value);
}

function loadEnvFile(string $path): void {
    if (!is_file($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        $parts = explode('=', $line, 2);

        if (count($parts) !== 2) {
            continue;
        }

        [$name, $value] = $parts;
        $name = trim($name);
        $value = trim($value);

        if ($name === '') {
            continue;
        }

        $value = preg_replace('/^"(.*)"$/', '$1', $value);
        $value = preg_replace('/^\'(.*)\'$/', '$1', $value);

        putenv($name . '=' . $value);
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

$projectRoot = dirname(__DIR__, 2);
loadEnvFile($projectRoot . '/.env');

if (!defined('APP_ROOT')) {
    define('APP_ROOT', $projectRoot);
}

if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $hostName = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('BASE_URL', env('APP_URL', $protocol . $hostName));
}
