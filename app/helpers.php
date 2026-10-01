<?php

function asset_url(string $path): string
{
    $base = rtrim(BASE_URL, '/');
    $relative = ltrim($path, '/');
    return $base . '/assets/' . $relative;
}

function page_url(string $page): string
{
    $base = rtrim(BASE_URL, '/');
    return $base . '/' . ltrim($page, '/');
}
