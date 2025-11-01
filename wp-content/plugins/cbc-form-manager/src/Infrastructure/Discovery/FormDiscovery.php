<?php
namespace CbcFormManager\Infrastructure\Discovery;

use CbcFormManager\Application\FormModuleInterface;

if (!defined('ABSPATH')) { exit; }

class FormDiscovery
{
    /**
     * Discover all presentation forms under presentation/*Form.php.
     * @return FormModuleInterface[]
     */
    public function discover(): array
    {
        $forms = [];
        $pattern = CBC_FM_PLUGIN_DIR . 'presentation' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'Form.php';
        foreach (glob($pattern) as $file) {
            $dir = basename(dirname($file));
            $class = '\\CbcFormManager\\Presentation\\' . $dir . '\\Form';
            if (class_exists($class)) {
                try {
                    $instance = new $class();
                    if ($instance instanceof FormModuleInterface) {
                        $forms[] = $instance;
                    }
                } catch (\Throwable $e) {
                    // Skip on error creating instance
                }
            }
        }
        return $forms;
    }
}

