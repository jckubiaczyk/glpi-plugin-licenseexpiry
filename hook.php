<?php

function plugin_licenseexpiry_install()
{
    global $DB;

    // Create config table
    if (!$DB->tableExists('glpi_plugin_licenseexpiry_configs')) {
        $query = "CREATE TABLE `glpi_plugin_licenseexpiry_configs` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `alert_days_orange` int(10) unsigned NOT NULL DEFAULT 30,
            `alert_days_red` int(10) unsigned NOT NULL DEFAULT 0,
            `notify_enabled` tinyint(1) NOT NULL DEFAULT 1,
            `notify_frequency_days` int(10) unsigned NOT NULL DEFAULT 1,
            `notify_email` varchar(255) DEFAULT NULL,
            `color_expired` varchar(7) NOT NULL DEFAULT '#ffcdd2',
            `color_expired_text` varchar(7) NOT NULL DEFAULT '#b71c1c',
            `color_warning` varchar(7) NOT NULL DEFAULT '#ffe0b2',
            `color_warning_text` varchar(7) NOT NULL DEFAULT '#e65100',
            `color_valid` varchar(7) NOT NULL DEFAULT '#c8e6c9',
            `color_valid_text` varchar(7) NOT NULL DEFAULT '#1b5e20',
            `date_mod` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $DB->doQuery($query);

        // Insert default config
        $DB->insert('glpi_plugin_licenseexpiry_configs', [
            'id'                   => 1,
            'alert_days_orange'    => 30,
            'alert_days_red'       => 0,
            'notify_enabled'       => 1,
            'notify_frequency_days' => 1,
            'notify_email'         => '',
            'date_mod'             => date('Y-m-d H:i:s'),
        ]);
    }

    // Enable license alerts on root entity if not already
    $entity = new Entity();
    $entity->getFromDB(0);
    if ($entity->fields['use_licenses_alert'] == 0) {
        $entity->update([
            'id'                              => 0,
            'use_licenses_alert'              => 1,
            'send_licenses_alert_before_delay' => 30 * DAY_TIMESTAMP,
        ]);
    }

    // Activate the software cron task
    $crontask = new CronTask();
    if ($crontask->getFromDBbyName('SoftwareLicense', 'software')) {
        if ($crontask->fields['state'] == 0) {
            $crontask->update([
                'id'    => $crontask->fields['id'],
                'state' => 1,
            ]);
        }
    }

    // Add dashboard card to Central dashboard if not already present
    $dashboard = new \Glpi\Dashboard\Dashboard();
    if ($dashboard->getFromDB('central')) {
        $dashboard_id = $dashboard->fields['id'];
    } else {
        // Try by name
        $iterator = $DB->request([
            'FROM'  => 'glpi_dashboards_dashboards',
            'WHERE' => ['name' => 'Central'],
            'LIMIT' => 1,
        ]);
        $dashboard_id = count($iterator) ? $iterator->current()['id'] : null;
    }

    if ($dashboard_id) {
        $existing = $DB->request([
            'FROM'  => 'glpi_dashboards_items',
            'WHERE' => [
                'dashboards_dashboards_id' => $dashboard_id,
                'card_id'                  => 'plugin_licenseexpiry_table',
            ],
        ]);
        if (count($existing) === 0) {
            // Find the max Y position to place the card at the bottom
            $max_y = 0;
            $rows = $DB->request([
                'SELECT' => ['y', 'height'],
                'FROM'   => 'glpi_dashboards_items',
                'WHERE'  => ['dashboards_dashboards_id' => $dashboard_id],
            ]);
            foreach ($rows as $row) {
                $bottom = (int)$row['y'] + (int)$row['height'];
                if ($bottom > $max_y) {
                    $max_y = $bottom;
                }
            }

            $uid = 'plugin_licenseexpiry_table_' . bin2hex(random_bytes(8));
            $DB->insert('glpi_dashboards_items', [
                'dashboards_dashboards_id' => $dashboard_id,
                'gridstack_id'             => $uid,
                'card_id'                  => 'plugin_licenseexpiry_table',
                'x'                        => 0,
                'y'                        => $max_y,
                'width'                    => 14,
                'height'                   => 6,
                'card_options'             => json_encode([
                    'color'      => '#ffffff',
                    'widgettype' => 'licenseExpiryTable',
                ]),
            ]);
        }
    }

    // Ensure admin target exists on notification
    $notif = new Notification();
    if ($notif->getFromDBByCrit(['itemtype' => 'SoftwareLicense', 'event' => 'alert'])) {
        // Check if GLOBAL_ADMINISTRATOR target exists
        $target = new NotificationTarget();
        if (!$target->getFromDBByCrit([
            'notifications_id' => $notif->fields['id'],
            'type'             => Notification::USER_TYPE,
            'items_id'         => Notification::GLOBAL_ADMINISTRATOR,
        ])) {
            $target->add([
                'notifications_id' => $notif->fields['id'],
                'type'             => Notification::USER_TYPE,
                'items_id'         => Notification::GLOBAL_ADMINISTRATOR,
            ]);
        }
    }

    return true;
}

function plugin_licenseexpiry_uninstall()
{
    global $DB;

    $DB->doQuery("DROP TABLE IF EXISTS `glpi_plugin_licenseexpiry_configs`");

    // Remove dashboard card
    $DB->doQuery("DELETE FROM `glpi_dashboards_items` WHERE `card_id` = 'plugin_licenseexpiry_table'");

    return true;
}

function plugin_licenseexpiry_deactivate()
{
    global $DB;
    $DB->doQuery("DELETE FROM `glpi_dashboards_items` WHERE `card_id` = 'plugin_licenseexpiry_table'");
}

function plugin_licenseexpiry_activate()
{
    global $DB;

    $dashboard = new \Glpi\Dashboard\Dashboard();
    if ($dashboard->getFromDB('central')) {
        $dashboard_id = $dashboard->fields['id'];

        $existing = $DB->request([
            'FROM'  => 'glpi_dashboards_items',
            'WHERE' => [
                'dashboards_dashboards_id' => $dashboard_id,
                'card_id'                  => 'plugin_licenseexpiry_table',
            ],
        ]);

        if (count($existing) === 0) {
            $max_y = 0;
            $rows = $DB->request([
                'SELECT' => ['y', 'height'],
                'FROM'   => 'glpi_dashboards_items',
                'WHERE'  => ['dashboards_dashboards_id' => $dashboard_id],
            ]);
            foreach ($rows as $row) {
                $bottom = (int)$row['y'] + (int)$row['height'];
                if ($bottom > $max_y) {
                    $max_y = $bottom;
                }
            }

            $uid = 'plugin_licenseexpiry_table_' . bin2hex(random_bytes(8));
            $DB->insert('glpi_dashboards_items', [
                'dashboards_dashboards_id' => $dashboard_id,
                'gridstack_id'             => $uid,
                'card_id'                  => 'plugin_licenseexpiry_table',
                'x'                        => 0,
                'y'                        => $max_y,
                'width'                    => 14,
                'height'                   => 6,
                'card_options'             => json_encode([
                    'color'      => '#ffffff',
                    'widgettype' => 'licenseExpiryTable',
                ]),
            ]);
        }
    }
}

function plugin_licenseexpiry_dashboard_cards(?array $cards = null)
{
    if ($cards === null) {
        $cards = [];
    }

    $cards['plugin_licenseexpiry_table'] = [
        'widgettype'   => ['licenseExpiryTable'],
        'itemtype'     => '\\SoftwareLicense',
        'group'        => __('Assets'),
        'label'        => __('License Expiry', 'licenseexpiry'),
        'provider'     => 'PluginLicenseexpiryDashboard::getCardHtml',
        'cache'        => false,
    ];

    return $cards;
}

function plugin_licenseexpiry_dashboard_types(?array $types = null)
{
    if ($types === null) {
        $types = [];
    }

    $types['licenseExpiryTable'] = [
        'label'    => 'License Expiry Table',
        'function' => 'PluginLicenseexpiryDashboard::renderWidget',
        'image'    => '',
        'width'    => 6,
        'height'   => 4,
    ];

    return $types;
}
