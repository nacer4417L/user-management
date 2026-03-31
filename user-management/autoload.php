<?php
/**
 * PSR-4 style autoloader.
 * Maps namespace root "App\" → /App/ directory relative to this file.
 */
spl_autoload_register(function (string $class): void {
    $file = __DIR__ . '/' . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
