<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v6_4_5__v6_4_6_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->_installer;
        $connection = $installer->getConnection();

        $amazonAccountTableName = $installer->getTablesObject()->getFullName('amazon_account');

        if ($installer->getTableModifier('amazon_account')->isColumnExists('orders_last_synchronization') &&
            $installer->getTableModifier('amazon_account')->isColumnExists('merchant_id')) {

            $result = $connection->query(<<<SQL
    SELECT aa.merchant_id,
           MIN(aa.orders_last_synchronization) as orders_last_synchronization
    FROM {$amazonAccountTableName} as aa
    WHERE aa.orders_last_synchronization IS NOT NULL
    GROUP BY aa.merchant_id
SQL
            )->fetchAll(PDO::FETCH_ASSOC);

            foreach ($result as $item) {
                $installer->getSynchConfigModifier()->insert(
                    "/amazon/orders/receive/{$item['merchant_id']}/",
                    "from_update_date",
                    $item['orders_last_synchronization']
                );
            }

            $installer->getTableModifier('amazon_account')->dropColumn('orders_last_synchronization');
        }

        // ---------------------------------------

        $installer->getSynchConfigModifier()->insert(
            '/amazon/orders/update/', 'interval', '1800', 'in seconds'
        );

        // ---------------------------------------

        $installer->getTableModifier('ebay_account')->addColumn(
            'job_token', 'VARCHAR(255)', NULL, 'ebay_shipping_discount_profiles'
        );
    }

    //########################################
}