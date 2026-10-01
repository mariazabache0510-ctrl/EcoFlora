<?php

class PageController
{
    public static function render(string $viewName): void
    {
        $viewPath = dirname(__DIR__) . '/views/pages/' . $viewName . '.php';

        if (!is_file($viewPath)) {
            throw new RuntimeException('View not found: ' . $viewName);
        }

        require $viewPath;
    }
}
