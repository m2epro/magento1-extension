<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

$tempTable = $installer->getTable('m2epro_ebay_template_synchronization');

$queryStmt = $connection->query("
    SELECT `template_synchronization_id`,
           `schedule_week_settings`
    FROM `{$tempTable}`
    WHERE `schedule_week_settings` IS NOT NULL
    AND `schedule_week_settings` <> '[]'
");

$preparedData = array();
while ($row = $queryStmt->fetch()) {

    $settings = (array)json_decode($row['schedule_week_settings'], true);

    foreach ($settings as &$daySettings) {
        $daySettings['time_from'] = Mage::getModel('core/date')->date('H:i:s',$daySettings['time_from']);
        $daySettings['time_to'] = Mage::getModel('core/date')->date('H:i:s',$daySettings['time_to']);
    }

    $preparedData[(int)$row['template_synchronization_id']] = json_encode($settings);
}

foreach ($preparedData as $templateId => $weekSettings) {
    $connection->query("
        UPDATE `{$tempTable}`
        SET `schedule_week_settings` = '{$weekSettings}'
        WHERE `template_synchronization_id` = {$templateId}
    ");
}

//#############################################

$installer->endSetup();

//#############################################