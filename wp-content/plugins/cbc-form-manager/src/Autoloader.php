<?php
namespace CbcFormManager;

if (!defined('ABSPATH')) { exit; }

class Autoloader
{
    public static function register(): void
    {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    public static function autoload(string $class): void
    {
        $prefix = __NAMESPACE__ . '\\';
        if (strpos($class, $prefix) !== 0) {
            return;
        }

        $relative = substr($class, strlen($prefix));
        $relativeNorm = str_replace('\\', DIRECTORY_SEPARATOR, $relative);

        // Route Presentation namespace to /presentation, others to /src
        if (strpos($relative, 'Presentation\\') === 0) {
            $relativePresentation = substr($relative, strlen('Presentation\\'));
            $relativePresentationPath = str_replace('\\', DIRECTORY_SEPARATOR, $relativePresentation) . '.php';
            $file = CBC_FM_PLUGIN_DIR . 'presentation' . DIRECTORY_SEPARATOR . $relativePresentationPath;
        } else {
            $file = CBC_FM_PLUGIN_DIR . 'src' . DIRECTORY_SEPARATOR . $relativeNorm . '.php';
        }

        if (is_readable($file)) {
            require_once $file;
        }
    }
}

