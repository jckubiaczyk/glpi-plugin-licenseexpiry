<?php

class PluginLicenseexpiryLang
{
    private static $strings = null;

    public static function t($key)
    {
        if (self::$strings === null) {
            self::load();
        }
        return self::$strings[$key] ?? $key;
    }

    private static function load()
    {
        $glpi_lang = $_SESSION['glpilanguage'] ?? 'fr_FR';
        $plugin_dir = Plugin::getPhpDir('licenseexpiry');
        $file = $plugin_dir . '/locales/' . $glpi_lang . '.php';

        if (!file_exists($file)) {
            // Fallback: try base language (e.g. en_GB for en_US)
            $base = substr($glpi_lang, 0, 2);
            $fallbacks = glob($plugin_dir . '/locales/' . $base . '_*.php');
            $file = !empty($fallbacks) ? $fallbacks[0] : $plugin_dir . '/locales/en_GB.php';
        }

        if (file_exists($file)) {
            $lang = [];
            include $file;
            self::$strings = $lang;
        } else {
            self::$strings = [];
        }
    }
}
