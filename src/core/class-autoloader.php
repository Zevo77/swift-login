<?php

namespace Swift_Login\Core;

defined('ABSPATH') || exit;

class Autoloader
{
    private static $map = [];

    public static function register(): void
    {
        spl_autoload_register([static::class, 'load']);
    }

    public static function load(string $class): void
    {
        if (strpos($class, 'Swift_Login\\') !== 0) {
            return;
        }

        // Convert namespace to file path
        $relative = substr($class, strlen('Swift_Login\\'));
        $parts    = explode('\\', $relative);

        // Map namespace segments to directories
        $ns_map = [
            'Core'     => 'core',
            'Includes' => 'includes',
            'Ajax'     => 'ajax',
            'Hooks'    => 'hooks',
            'Actions'  => 'actions',
            'Models'   => 'models',
            'Views'    => 'views',
            'Jobs'     => 'jobs',
        ];

        if (empty($parts)) {
            return;
        }

        $dir = isset($ns_map[$parts[0]]) ? $ns_map[$parts[0]] : strtolower($parts[0]);
        $class_name = end($parts);

        // Convert class name to file name: Class_Name -> class-name.php
        $file_name = 'class-' . strtolower(str_replace('_', '-', $class_name)) . '.php';

        $file = SWIFT_LOGIN_SRC_DIR . '/' . $dir . '/' . $file_name;

        if (file_exists($file)) {
            require_once $file;
        }
    }
}

Autoloader::register();
