<?php

class Ess_M2ePro_Sql_Upgrade_v5_2_2__v5_2_3_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->installer;
        $connection = $installer->getConnection();

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
    }

    //########################################
}