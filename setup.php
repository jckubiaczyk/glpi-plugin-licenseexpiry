<?php

use Glpi\Plugin\Hooks;

define('PLUGIN_LICENSEEXPIRY_VERSION', '1.0.0');
define('PLUGIN_LICENSEEXPIRY_MIN_GLPI', '11.0');
define('PLUGIN_LICENSEEXPIRY_MAX_GLPI', '11.99');

function plugin_init_licenseexpiry()
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS[Hooks::CSRF_COMPLIANT]['licenseexpiry'] = true;

    // Dashboard cards and widget types
    $PLUGIN_HOOKS[Hooks::DASHBOARD_CARDS]['licenseexpiry'] = 'plugin_licenseexpiry_dashboard_cards';
    $PLUGIN_HOOKS[Hooks::DASHBOARD_TYPES]['licenseexpiry'] = 'plugin_licenseexpiry_dashboard_types';

    // Configuration page
    if (Session::haveRight('config', UPDATE)) {
        $PLUGIN_HOOKS['config_page']['licenseexpiry'] = 'front/config.form.php';
    }

    // Menu
    $PLUGIN_HOOKS['menu_toadd']['licenseexpiry'] = ['config' => 'PluginLicenseexpiryConfig'];
}

function plugin_version_licenseexpiry()
{
    return [
        'name'           => __('License Alerts', 'licenseexpiry'),
        'version'        => PLUGIN_LICENSEEXPIRY_VERSION,
        'author'         => 'Nidaplast',
        'license'        => 'GPLv2+',
        'homepage'       => '',
        'requirements'   => [
            'glpi' => [
                'min' => PLUGIN_LICENSEEXPIRY_MIN_GLPI,
                'max' => PLUGIN_LICENSEEXPIRY_MAX_GLPI,
            ],
        ],
    ];
}
