<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

$tempTable = $installer->getTable('m2epro_amazon_account');
$tempAccounts = $connection->query("SELECT * FROM `{$tempTable}`")->fetchAll();

foreach ($tempAccounts as $account) {
    $magentoOrdersSettings = json_decode($account['magento_orders_settings'], true);

    if (!is_array($magentoOrdersSettings)) {
        continue;
    }

    if (isset($magentoOrdersSettings['customer']['billing_address_mode'])) {
        // upgrade already performed
        continue;
    }

    $magentoOrdersSettings['customer']['billing_address_mode'] = 0;

    $magentoOrdersSettings = $connection->quote(json_encode($magentoOrdersSettings));

    $connection->query(
        "UPDATE `{$tempTable}`
         SET `magento_orders_settings` = {$magentoOrdersSettings}
         WHERE `account_id` = ".(int)$account['account_id']
    );
}

//#############################################

$installer->endSetup();

//#############################################