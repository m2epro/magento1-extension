<?php

//########################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//########################################

$installer->getMainConfigModifier()->delete('/cron/task/amazon/repricing/synchronize_general/');
$installer->getMainConfigModifier()->delete('/cron/task/amazon/repricing/synchronize_actual_price/');

$installer->getMainConfigModifier()->insert('/cron/task/amazon/repricing/synchronize/', 'mode', '1');
$installer->getMainConfigModifier()->insert('/cron/task/amazon/repricing/synchronize/', 'interval', '60');
$installer->getMainConfigModifier()->insert('/cron/task/amazon/repricing/synchronize/', 'last_access', NULL);
$installer->getMainConfigModifier()->insert('/cron/task/amazon/repricing/synchronize/', 'last_run', NULL);

//########################################

$installer->getTableModifier('listing_product_instruction')->addIndex('create_date');
$installer->getTableModifier('listing_product_scheduled_action')->addIndex('create_date');

$connection->addIndex(
    $installer->getTablesObject()->getFullName('listing_product_scheduled_action'),
    'listing_product_id', 'listing_product_id', Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->run(<<<SQL

DELETE FROM `m2epro_lock_transactional`
WHERE `nick` LIKE 'scheduled_manager%'

SQL
);

//########################################

$installer->run(<<<SQL

    UPDATE `m2epro_marketplace`
    SET `group_title` = 'Australia Region'
    WHERE `id` IN (4, 35)

SQL
);

$connection->update(
    $installer->getTablesObject()->getFullName('amazon_marketplace'),
    array('is_business_available' => 1),
    array('marketplace_id = ?' => 26) // FR
);

//########################################

$installer->getTableModifier('amazon_listing_product_action_processing')
    ->addColumn('is_prepared', 'TINYINT(2) NOT NULL', '0', 'type', true, false)
    ->addColumn('group_hash', 'VARCHAR(255)', 'NULL', 'is_prepared', true, false)
    ->changeColumn('request_data', 'LONGTEXT', 'NULL', NULL, false)
    ->commit();

$connection->update(
    $installer->getTablesObject()->getFullName('amazon_listing_product_action_processing'),
    array('is_prepared' => 1)
);

$stmt = $connection->select()
    ->from(
        array('alpap' => $installer->getTablesObject()->getFullName('amazon_listing_product_action_processing')),
        array('id', 'listing_product_id', 'type')
    )
    ->joinLeft(
        array('lp' => $installer->getTablesObject()->getFullName('listing_product')),
        'lp.id = alpap.listing_product_id',
        array()
    )
    ->joinLeft(
        array('l' => $installer->getTablesObject()->getFullName('listing')),
        'l.id = lp.listing_id',
        array('account_id')
    )
    ->where('alpap.group_hash IS NULL')->query();

$updateListingProductIds = array();

while ($actionData = $stmt->fetch()) {
    $updateListingProductIds[$actionData['account_id']][$actionData['type']][] = $actionData['listing_product_id'];
}

foreach ($updateListingProductIds as $accountId => $accountActionsData) {
    foreach ($accountActionsData as $actionType => $listingProductIds) {
        if ($actionType == 'delete') {
            $maxGroupSize = 10000;
        } else {
            $maxGroupSize = 1000;
        }

        $listingProductIdsGroups = array_chunk($listingProductIds, $maxGroupSize);

        foreach ($listingProductIdsGroups as $listingProductIdsGroup) {
            $groupHash = sha1(microtime());

            $connection->update(
                $installer->getTablesObject()->getFullName('amazon_listing_product_action_processing'),
                array('group_hash' => $groupHash),
                array('listing_product_id IN (?)' => $listingProductIdsGroup)
            );
        }
    }
}

$installer->getMainConfigModifier()->insert('/amazon/listing/product/action/scheduled_data/', 'limit', '20000');
$installer->getMainConfigModifier()->insert(
    '/amazon/listing/product/action/processing/prepare/', 'max_listings_products_count', '2000'
);

//########################################

$installer->endSetup();

//########################################