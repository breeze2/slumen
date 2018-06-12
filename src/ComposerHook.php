<?php
namespace BL\Slumen;

class ComposerHook
{
    const DEFAULT_BOOTSTRAP_FILE_NAME = 'slumen.php';

    public static function postPackageInstall($file = self::DEFAULT_BOOTSTRAP_FILE_NAME)
    {
        self::defineComposerInstallPath();

        if (!COMPOSER_VENDOR_PATH) {
            return false;
        }
        $bootstrap_path = COMPOSER_VENDOR_PATH . '/../bootstrap';
        $bootstrap_file = $bootstrap_path . '/' . $file;
        $source         = realpath(COMPOSER_VENDOR_PATH . '/breeze2/slumen/bootstrap/slumen.php');

        if ($source && !file_exists($bootstrap_file)) {
            copy($source, realpath($bootstrap_path) . '/' . $file);
        }
        return true;
    }

    public static function postPackageUninstall()
    {

    }

    protected static function defineComposerInstallPath()
    {
        define('COMPOSER_VENDOR_PATH', realpath(__DIR__ . '/../../../../vendor'));
    }

}
