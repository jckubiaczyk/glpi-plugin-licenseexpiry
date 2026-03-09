<?php

include('../../../inc/includes.php');

Session::checkRight('config', UPDATE);

if (isset($_POST['update'])) {
    PluginLicenseexpiryConfig::updateConfig($_POST);
    Html::back();
}

Html::header(
    PluginLicenseexpiryLang::t('plugin_name'),
    $_SERVER['PHP_SELF'],
    'config',
    'PluginLicenseexpiryConfig'
);

PluginLicenseexpiryConfig::showConfigForm();

Html::footer();
