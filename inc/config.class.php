<?php

class PluginLicenseexpiryConfig extends CommonDBTM
{
    static $rightname = 'config';

    public static function getTypeName($nb = 0)
    {
        return PluginLicenseexpiryLang::t('plugin_name');
    }

    public static function getIcon()
    {
        return 'ti ti-license';
    }

    public static function canCreate(): bool
    {
        return Session::haveRight('config', UPDATE);
    }

    public static function canView(): bool
    {
        return Session::haveRight('config', READ);
    }

    public static function getDefaults()
    {
        return [
            'alert_days_orange'     => 30,
            'alert_days_red'        => 0,
            'notify_enabled'        => 1,
            'notify_frequency_days' => 1,
            'notify_email'          => '',
            'color_expired'         => '#ffcdd2',
            'color_expired_text'    => '#b71c1c',
            'color_warning'         => '#ffe0b2',
            'color_warning_text'    => '#e65100',
            'color_valid'           => '#c8e6c9',
            'color_valid_text'      => '#1b5e20',
        ];
    }

    public static function getConfig()
    {
        global $DB;

        $config = new self();
        if (!$DB->tableExists('glpi_plugin_licenseexpiry_configs')) {
            return self::getDefaults();
        }

        $iterator = $DB->request([
            'FROM'  => 'glpi_plugin_licenseexpiry_configs',
            'WHERE' => ['id' => 1],
        ]);

        if (count($iterator)) {
            return array_merge(self::getDefaults(), $iterator->current());
        }

        return self::getDefaults();
    }

    public static function showConfigForm()
    {
        $t = 'PluginLicenseexpiryLang::t';
        $config = self::getConfig();

        echo "<div class='center'>";
        echo "<form method='post' action='" . Plugin::getPhpDir('licenseexpiry', false) . "/front/config.form.php'>";

        echo "<table class='tab_cadre_fixe'>";
        echo "<tr class='headerRow'><th colspan='2'><i class='" . self::getIcon() . "'></i> " . $t('config_title') . "</th></tr>";

        // Dashboard settings
        echo "<tr class='tab_bg_1'><th colspan='2'>" . $t('dashboard') . "</th></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . $t('alert_threshold') . "</td>";
        echo "<td><input type='number' name='alert_days_orange' value='" . (int)$config['alert_days_orange'] . "' min='1' max='365' class='form-control' style='width:100px;display:inline;'> " . $t('days') . "</td>";
        echo "</tr>";

        // Color settings
        echo "<tr class='tab_bg_1'><th colspan='2'>" . $t('colors') . "</th></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . $t('color_expired') . "</td>";
        echo "<td>";
        echo "<input type='color' name='color_expired' value='" . htmlspecialchars($config['color_expired']) . "' style='width:50px;height:30px;border:1px solid #ccc;border-radius:4px;cursor:pointer;vertical-align:middle;'> " . $t('background') . " &nbsp;&nbsp;";
        echo "<input type='color' name='color_expired_text' value='" . htmlspecialchars($config['color_expired_text']) . "' style='width:50px;height:30px;border:1px solid #ccc;border-radius:4px;cursor:pointer;vertical-align:middle;'> " . $t('text');
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . $t('color_warning') . "</td>";
        echo "<td>";
        echo "<input type='color' name='color_warning' value='" . htmlspecialchars($config['color_warning']) . "' style='width:50px;height:30px;border:1px solid #ccc;border-radius:4px;cursor:pointer;vertical-align:middle;'> " . $t('background') . " &nbsp;&nbsp;";
        echo "<input type='color' name='color_warning_text' value='" . htmlspecialchars($config['color_warning_text']) . "' style='width:50px;height:30px;border:1px solid #ccc;border-radius:4px;cursor:pointer;vertical-align:middle;'> " . $t('text');
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . $t('color_valid') . "</td>";
        echo "<td>";
        echo "<input type='color' name='color_valid' value='" . htmlspecialchars($config['color_valid']) . "' style='width:50px;height:30px;border:1px solid #ccc;border-radius:4px;cursor:pointer;vertical-align:middle;'> " . $t('background') . " &nbsp;&nbsp;";
        echo "<input type='color' name='color_valid_text' value='" . htmlspecialchars($config['color_valid_text']) . "' style='width:50px;height:30px;border:1px solid #ccc;border-radius:4px;cursor:pointer;vertical-align:middle;'> " . $t('text');
        echo "</td></tr>";

        // Notification settings
        echo "<tr class='tab_bg_1'><th colspan='2'>" . $t('notifications') . "</th></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . $t('notify_enable') . "</td>";
        echo "<td>";
        Dropdown::showYesNo('notify_enabled', $config['notify_enabled']);
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . $t('notify_frequency') . "</td>";
        echo "<td><input type='number' name='notify_frequency_days' value='" . (int)$config['notify_frequency_days'] . "' min='1' max='30' class='form-control' style='width:100px;display:inline;'> " . $t('notify_frequency_unit') . "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . $t('notify_extra_email') . "</td>";
        echo "<td><input type='email' name='notify_email' value='" . htmlspecialchars($config['notify_email'] ?? '') . "' class='form-control' style='width:300px;display:inline;' placeholder='optionnel@example.com'></td>";
        echo "</tr>";

        // Info section
        echo "<tr class='tab_bg_1'><th colspan='2'>" . $t('info') . "</th></tr>";

        global $DB;
        $today = date('Y-m-d');
        $orange_date = date('Y-m-d', strtotime('+' . (int)$config['alert_days_orange'] . ' days'));

        $expired = $DB->request([
            'COUNT' => 'cnt',
            'FROM'  => 'glpi_softwarelicenses',
            'WHERE' => [
                'is_deleted' => 0,
                'NOT' => ['expire' => null],
                ['NOT' => ['expire' => '0000-00-00']],
                ['expire' => ['<', $today]],
            ],
        ])->current()['cnt'];

        $expiring = $DB->request([
            'COUNT' => 'cnt',
            'FROM'  => 'glpi_softwarelicenses',
            'WHERE' => [
                'is_deleted' => 0,
                'NOT' => ['expire' => null],
                ['NOT' => ['expire' => '0000-00-00']],
                ['expire' => ['>=', $today]],
                ['expire' => ['<=', $orange_date]],
            ],
        ])->current()['cnt'];

        $valid = $DB->request([
            'COUNT' => 'cnt',
            'FROM'  => 'glpi_softwarelicenses',
            'WHERE' => [
                'is_deleted' => 0,
                'NOT' => ['expire' => null],
                ['NOT' => ['expire' => '0000-00-00']],
                ['expire' => ['>', $orange_date]],
            ],
        ])->current()['cnt'];

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . $t('expired_licenses') . "</td>";
        echo "<td><span style='background:#c62828;color:#fff;padding:3px 12px;border-radius:10px;'>$expired</span></td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . sprintf($t('expiring_licenses'), (int)$config['alert_days_orange']) . "</td>";
        echo "<td><span style='background:#ef6c00;color:#fff;padding:3px 12px;border-radius:10px;'>$expiring</span></td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . $t('valid_licenses') . "</td>";
        echo "<td><span style='background:#2e7d32;color:#fff;padding:3px 12px;border-radius:10px;'>$valid</span></td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2' class='center'>";
        echo "<input type='hidden' name='_glpi_csrf_token' value='" . Session::getNewCSRFToken() . "'>";
        echo "<button type='submit' name='update' class='btn btn-primary'>" . $t('save') . "</button>";
        echo "</td></tr>";

        echo "</table>";
        echo "</form>";
        echo "</div>";
    }

    public static function updateConfig($input)
    {
        global $DB;

        $DB->update('glpi_plugin_licenseexpiry_configs', [
            'alert_days_orange'     => (int)($input['alert_days_orange'] ?? 30),
            'notify_enabled'        => (int)($input['notify_enabled'] ?? 1),
            'notify_frequency_days' => (int)($input['notify_frequency_days'] ?? 1),
            'notify_email'          => $DB->escape($input['notify_email'] ?? ''),
            'color_expired'         => self::sanitizeColor($input['color_expired'] ?? '#ffcdd2'),
            'color_expired_text'    => self::sanitizeColor($input['color_expired_text'] ?? '#b71c1c'),
            'color_warning'         => self::sanitizeColor($input['color_warning'] ?? '#ffe0b2'),
            'color_warning_text'    => self::sanitizeColor($input['color_warning_text'] ?? '#e65100'),
            'color_valid'           => self::sanitizeColor($input['color_valid'] ?? '#c8e6c9'),
            'color_valid_text'      => self::sanitizeColor($input['color_valid_text'] ?? '#1b5e20'),
            'date_mod'              => date('Y-m-d H:i:s'),
        ], ['id' => 1]);

        $entity = new Entity();
        $entity->update([
            'id'                               => 0,
            'use_licenses_alert'               => (int)($input['notify_enabled'] ?? 1),
            'send_licenses_alert_before_delay'  => (int)($input['alert_days_orange'] ?? 30) * DAY_TIMESTAMP,
        ]);

        $crontask = new CronTask();
        if ($crontask->getFromDBbyName('SoftwareLicense', 'software')) {
            $crontask->update([
                'id'        => $crontask->fields['id'],
                'state'     => (int)($input['notify_enabled'] ?? 1),
                'frequency' => (int)($input['notify_frequency_days'] ?? 1) * DAY_TIMESTAMP,
            ]);
        }

        Session::addMessageAfterRedirect(PluginLicenseexpiryLang::t('config_saved'), true, INFO);
    }

    private static function sanitizeColor($color)
    {
        if (preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            return $color;
        }
        return '#cccccc';
    }
}
