<?php

class PluginLicenseexpiryDashboard
{
    /**
     * Provider function - returns widget arguments
     */
    public static function getCardHtml($card_options = [])
    {
        return [
            'label' => PluginLicenseexpiryLang::t('card_title'),
            'icon'  => PluginLicenseexpiryConfig::getIcon(),
        ];
    }

    /**
     * Widget render function - generates the HTML
     */
    public static function renderWidget(array $params = []): string
    {
        global $DB;

        $t = 'PluginLicenseexpiryLang::t';
        $config = PluginLicenseexpiryConfig::getConfig();
        $alert_days = (int)($config['alert_days_orange'] ?? 30);
        $today = date('Y-m-d');
        $orange_date = date('Y-m-d', strtotime("+{$alert_days} days"));

        $colors = [
            'expired_bg'  => $config['color_expired'] ?? '#ffcdd2',
            'expired_txt' => $config['color_expired_text'] ?? '#b71c1c',
            'warning_bg'  => $config['color_warning'] ?? '#ffe0b2',
            'warning_txt' => $config['color_warning_text'] ?? '#e65100',
            'valid_bg'    => $config['color_valid'] ?? '#c8e6c9',
            'valid_txt'   => $config['color_valid_text'] ?? '#1b5e20',
        ];

        $iterator = $DB->request([
            'SELECT' => [
                'glpi_softwarelicenses.id',
                'glpi_softwarelicenses.name AS license_name',
                'glpi_softwarelicenses.serial',
                'glpi_softwarelicenses.expire',
                'glpi_softwares.name AS software_name',
            ],
            'FROM'      => 'glpi_softwarelicenses',
            'LEFT JOIN' => [
                'glpi_softwares' => [
                    'FKEY' => [
                        'glpi_softwarelicenses' => 'softwares_id',
                        'glpi_softwares'        => 'id',
                    ],
                ],
            ],
            'WHERE' => [
                'glpi_softwarelicenses.is_deleted' => 0,
                'NOT' => ['glpi_softwarelicenses.expire' => null],
                ['NOT' => ['glpi_softwarelicenses.expire' => '0000-00-00']],
            ],
            'ORDER' => ['glpi_softwarelicenses.expire ASC'],
        ]);

        $label = htmlspecialchars($params['label'] ?? $t('card_title'));
        $icon = htmlspecialchars($params['icon'] ?? 'ti ti-license');

        $html = "<div class='card' style='background:#fff;padding:0;overflow:auto;height:100%;'>";
        $html .= "<div style='padding:8px 12px;font-weight:bold;font-size:14px;border-bottom:1px solid #eee;'>";
        $html .= "<i class='{$icon}' style='margin-right:6px;'></i>{$label}</div>";
        $html .= "<table style='width:100%;border-collapse:collapse;font-size:13px;'>";
        $html .= "<thead><tr style='background:#f5f5f5;'>";
        $html .= "<th style='padding:8px;text-align:left;border-bottom:2px solid #ddd;'>" . $t('software') . "</th>";
        $html .= "<th style='padding:8px;text-align:left;border-bottom:2px solid #ddd;'>" . $t('license') . "</th>";
        $html .= "<th style='padding:8px;text-align:left;border-bottom:2px solid #ddd;'>" . $t('serial') . "</th>";
        $html .= "<th style='padding:8px;text-align:left;border-bottom:2px solid #ddd;'>" . $t('expiration') . "</th>";
        $html .= "</tr></thead><tbody>";

        $count = 0;
        foreach ($iterator as $row) {
            $expire = $row['expire'];
            if ($expire < $today) {
                $bg = $colors['expired_bg'];
                $color = $colors['expired_txt'];
            } elseif ($expire <= $orange_date) {
                $bg = $colors['warning_bg'];
                $color = $colors['warning_txt'];
            } else {
                $bg = $colors['valid_bg'];
                $color = $colors['valid_txt'];
            }

            $html .= "<tr style='background:{$bg};color:{$color};'>";
            $html .= "<td style='padding:6px 8px;'>" . htmlspecialchars($row['software_name'] ?? '') . "</td>";
            $html .= "<td style='padding:6px 8px;'>" . htmlspecialchars($row['license_name'] ?? '') . "</td>";
            $html .= "<td style='padding:6px 8px;'>" . htmlspecialchars($row['serial'] ?? '') . "</td>";
            $html .= "<td style='padding:6px 8px;font-weight:bold;'>" . htmlspecialchars($expire) . "</td>";
            $html .= "</tr>";
            $count++;
        }

        if ($count === 0) {
            $html .= "<tr><td colspan='4' style='padding:12px;text-align:center;color:#999;'>" . $t('no_license') . "</td></tr>";
        }

        $html .= "</tbody></table></div>";

        return $html;
    }
}
