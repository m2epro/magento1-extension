<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

/*
    RENAME TABLE `m2epro_ebay_motor_specific` TO `m2epro_ebay_dictionary_motor_specific`;

    ALTER TABLE `m2epro_ebay_dictionary_motor_specific`
    ADD COLUMN `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`id`),
    ADD INDEX `epid` (`epid`),
    ADD INDEX `marketplace_id` (`marketplace_id`);

    ALTER TABLE `m2epro_ebay_dictionary_marketplace`
    ADD COLUMN `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`id`),
    ADD INDEX `marketplace_id` (`marketplace_id`);

    ALTER TABLE `m2epro_ebay_dictionary_category`
    ADD COLUMN `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
    CHANGE COLUMN `parent_id` `parent_category_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`id`),
    DROP INDEX `parent_id`,
    ADD INDEX `marketplace_id` (`marketplace_id`),
    ADD INDEX `category_id` (`category_id`),
    ADD INDEX `parent_category_id` (`parent_category_id`);

    ALTER TABLE `m2epro_amazon_dictionary_category`
    CHANGE COLUMN `id` `category_id` int(11) UNSIGNED NOT NULL,
    CHANGE COLUMN `parent_id` `parent_category_id` int(11) UNSIGNED DEFAULT NULL,
    ADD COLUMN `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`id`),
    ADD INDEX `category_id` (`category_id`),
    ADD INDEX `parent_category_id` (`parent_category_id`);

    ALTER TABLE `m2epro_amazon_dictionary_marketplace`
    ADD COLUMN `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`id`),
    ADD INDEX `marketplace_id` (`marketplace_id`);

    ALTER TABLE `m2epro_amazon_dictionary_specific`
    CHANGE COLUMN `id` `specific_id` int(11) UNSIGNED NOT NULL,
    CHANGE COLUMN `parent_id` `parent_specific_id` int(11) UNSIGNED DEFAULT NULL,
    ADD COLUMN `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
    ADD COLUMN `marketplace_id` int(11) UNSIGNED NOT NULL AFTER `id`,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`id`),
    DROP INDEX `parent_id`,
    ADD INDEX `marketplace_id` (`marketplace_id`),
    ADD INDEX `specific_id` (`specific_id`),
    ADD INDEX `parent_specific_id` (`parent_specific_id`);

    ALTER TABLE `m2epro_buy_dictionary_category`
    CHANGE COLUMN `category_id` `native_id` varchar(255) DEFAULT NULL,
    CHANGE COLUMN `id` `category_id` int(11) UNSIGNED NOT NULL,
    CHANGE COLUMN `parent_id` `parent_category_id` int(11) UNSIGNED DEFAULT NULL,
    ADD COLUMN `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`id`),
    DROP INDEX `parent_id`,
    DROP INDEX `category_id`,
    ADD INDEX `native_id` (`native_id`),
    ADD INDEX `category_id` (`category_id`),
    ADD INDEX `parent_category_id` (`parent_category_id`);
*/

//---------------------------------------------

$motorsOldTableName = $installer->getTable('m2epro_ebay_motor_specific');
$motorsNewTableName = $installer->getTable('m2epro_ebay_dictionary_motor_specific');

if ($installer->tableExists($motorsOldTableName) &&
    !$installer->tableExists($motorsNewTableName)) {
    $query = sprintf('ALTER TABLE %s RENAME TO %s', $motorsOldTableName, $motorsNewTableName);
    $connection->query($query);
}

if ($installer->tableExists($motorsNewTableName)) {
    if ($connection->tableColumnExists($motorsNewTableName, 'id') === false) {
        $installer->run(<<<SQL
              ALTER TABLE `m2epro_ebay_dictionary_motor_specific`
              ADD COLUMN `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
              DROP PRIMARY KEY,
              ADD PRIMARY KEY (`id`);
SQL
        );
    }

    $newIndexes = array(
        'epid',
        'marketplace_id'
    );

    $indexList = $connection->getIndexList($motorsNewTableName);
    foreach ($newIndexes as $newIndex) {
        if (!isset($indexList[strtoupper($newIndex)])) {
            $connection->addKey($motorsNewTableName, $newIndex, $newIndex);
        }
    }
}

//---------------------------------------------

$dictionaryMarketplaceTableName = $installer->getTable('m2epro_ebay_dictionary_marketplace');

if ($installer->tableExists($dictionaryMarketplaceTableName)) {
    if ($connection->tableColumnExists($dictionaryMarketplaceTableName, 'id') === false) {
        $installer->run(<<<SQL
            ALTER TABLE `m2epro_ebay_dictionary_marketplace`
            ADD COLUMN `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
            DROP PRIMARY KEY,
            ADD PRIMARY KEY (`id`);
SQL
        );
    }

    $indexList = $connection->getIndexList($dictionaryMarketplaceTableName);

    if (!isset($indexList[strtoupper('marketplace_id')])) {
        $connection->addKey($dictionaryMarketplaceTableName, 'marketplace_id', 'marketplace_id');
    }
}

//---------------------------------------------

$dictionaryCategoryTableName = $installer->getTable('m2epro_ebay_dictionary_category');

if ($installer->tableExists($dictionaryCategoryTableName)) {
    if ($connection->tableColumnExists($dictionaryCategoryTableName, 'id') === false) {
        $installer->run(<<<SQL
          ALTER TABLE `m2epro_ebay_dictionary_category`
          ADD COLUMN `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
          DROP PRIMARY KEY,
          ADD PRIMARY KEY (`id`);
SQL
        );
    }

    if ($connection->tableColumnExists($dictionaryCategoryTableName, 'parent_id') !== false &&
        $connection->tableColumnExists($dictionaryCategoryTableName, 'parent_category_id') === false) {
        $connection->changeColumn(
                   $dictionaryCategoryTableName,
                       'parent_id',
                       'parent_category_id',
                       'int(11) UNSIGNED NOT NULL DEFAULT 0');
    }

    $indexList = $connection->getIndexList($dictionaryCategoryTableName);

    if (isset($indexList[strtoupper('parent_id')])) {
        $connection->dropKey($dictionaryCategoryTableName, 'parent_id');
    }

    $newIndexes = array(
        'marketplace_id',
        'category_id',
        'parent_category_id'
    );

    foreach ($newIndexes as $newIndex) {
        if (!isset($indexList[strtoupper($newIndex)])) {
            $connection->addKey($dictionaryCategoryTableName, $newIndex, $newIndex);
        }
    }

}

//---------------------------------------------

$amazonDictionaryCategoryTableName = $installer->getTable('m2epro_amazon_dictionary_category');

if ($installer->tableExists($amazonDictionaryCategoryTableName)) {

    if ($connection->tableColumnExists($amazonDictionaryCategoryTableName, 'id') !== false &&
        $connection->tableColumnExists($amazonDictionaryCategoryTableName, 'category_id') === false) {
        $connection->changeColumn(
                   $amazonDictionaryCategoryTableName,
                       'id',
                       'category_id',
                       'int(11) UNSIGNED NOT NULL');
    }

    if ($connection->tableColumnExists($amazonDictionaryCategoryTableName, 'parent_id') !== false &&
        $connection->tableColumnExists($amazonDictionaryCategoryTableName, 'parent_category_id') === false) {
        $connection->changeColumn(
                   $amazonDictionaryCategoryTableName,
                       'parent_id',
                       'parent_category_id',
                       'int(11) UNSIGNED DEFAULT NULL');
    }

    if ($connection->tableColumnExists($amazonDictionaryCategoryTableName, 'id') === false) {
        $installer->run(<<<SQL
          ALTER TABLE `m2epro_amazon_dictionary_category`
          ADD COLUMN `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
          DROP PRIMARY KEY,
          ADD PRIMARY KEY (`id`);
SQL
        );
    }

    $indexList = $connection->getIndexList($amazonDictionaryCategoryTableName);

    $newIndexes = array(
        'category_id',
        'parent_category_id'
    );

    foreach ($newIndexes as $newIndex) {
        if (!isset($indexList[strtoupper($newIndex)])) {
            $connection->addKey($amazonDictionaryCategoryTableName, $newIndex, $newIndex);
        }
    }
}

//---------------------------------------------

$amazonDictionaryMarketplaceTableName = $installer->getTable('m2epro_amazon_dictionary_marketplace');

if ($installer->tableExists($amazonDictionaryMarketplaceTableName)) {
    if ($connection->tableColumnExists($amazonDictionaryMarketplaceTableName, 'id') === false) {
        $installer->run(<<<SQL
            ALTER TABLE `m2epro_amazon_dictionary_marketplace`
            ADD COLUMN `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
            DROP PRIMARY KEY,
            ADD PRIMARY KEY (`id`);
SQL
        );
    }

    $indexList = $connection->getIndexList($amazonDictionaryMarketplaceTableName);

    if (!isset($indexList[strtoupper('marketplace_id')])) {
        $connection->addKey($amazonDictionaryMarketplaceTableName, 'marketplace_id', 'marketplace_id');
    }
}

//---------------------------------------------

$amazonDictionarySpecificTableName = $installer->getTable('m2epro_amazon_dictionary_specific');

if ($installer->tableExists($amazonDictionarySpecificTableName)) {

    if ($connection->tableColumnExists($amazonDictionarySpecificTableName, 'id') !== false &&
        $connection->tableColumnExists($amazonDictionarySpecificTableName, 'specific_id') === false) {
        $connection->changeColumn(
                   $amazonDictionarySpecificTableName,
                       'id',
                       'specific_id',
                       'int(11) UNSIGNED NOT NULL');
    }

    if ($connection->tableColumnExists($amazonDictionarySpecificTableName, 'parent_id') !== false &&
        $connection->tableColumnExists($amazonDictionarySpecificTableName, 'parent_specific_id') === false) {
        $connection->changeColumn(
                   $amazonDictionarySpecificTableName,
                       'parent_id',
                       'parent_specific_id',
                       'int(11) UNSIGNED DEFAULT NULL');
    }

    if ($connection->tableColumnExists($amazonDictionarySpecificTableName, 'id') === false) {
        $installer->run(<<<SQL
              ALTER TABLE `m2epro_amazon_dictionary_specific`
              ADD COLUMN `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
              DROP PRIMARY KEY,
              ADD PRIMARY KEY (`id`);
SQL
        );
    }

    if ($connection->tableColumnExists($amazonDictionarySpecificTableName, 'marketplace_id') === false) {
        $connection->addColumn(
                   $amazonDictionarySpecificTableName,
                       'marketplace_id',
                       'int(11) UNSIGNED NOT NULL AFTER `id`'
        );
    }

    $indexList = $connection->getIndexList($amazonDictionarySpecificTableName);

    if (isset($indexList[strtoupper('parent_id')])) {
        $connection->dropKey($amazonDictionarySpecificTableName, 'parent_id');
    }

    $newIndexes = array(
        'marketplace_id',
        'specific_id',
        'parent_specific_id'
    );

    foreach ($newIndexes as $newIndex) {
        if (!isset($indexList[strtoupper($newIndex)])) {
            $connection->addKey($amazonDictionarySpecificTableName, $newIndex, $newIndex);
        }
    }

    if ($connection->tableColumnExists($amazonDictionarySpecificTableName, 'marketplace_id') !== false) {

        $amazonDictionaryMarketplaceTableName = $installer->getTable('m2epro_amazon_dictionary_marketplace');

        $sql = $connection->query('SELECT `marketplace_id`, `nodes`
                                   FROM ' . $amazonDictionaryMarketplaceTableName);

        $marketplaceXsds = array();
        while ($row = $sql->fetch()) {
            $nodesData = json_decode($row['nodes'], true);

            foreach ($nodesData as $nodeData) {
                foreach ($nodeData['xsds'] as $xsd) {
                    $marketplaceXsds[$row['marketplace_id']][] = $connection->quote($xsd['hash']);
                }
            }

            if (isset($marketplaceXsds[$row['marketplace_id']])) {
                $marketplaceXsds[$row['marketplace_id']] = array_unique($marketplaceXsds[$row['marketplace_id']]);
            }
        }

        foreach ($marketplaceXsds as $marketplaceId => $xsds) {
            $xsds = implode(',', $xsds);
            $installer->run(<<<SQL
                      UPDATE m2epro_amazon_dictionary_specific
                      SET marketplace_id = {$marketplaceId}
                      WHERE xsd_hash IN ({$xsds})
SQL
            );
        }
    }
}

//---------------------------------------------

$buyDictionaryCategoryTableName = $installer->getTable('m2epro_buy_dictionary_category');

if ($installer->tableExists($buyDictionaryCategoryTableName)) {

    if ($connection->tableColumnExists($buyDictionaryCategoryTableName, 'category_id' !== false &&
        $connection->tableColumnExists($buyDictionaryCategoryTableName, 'native_id') === false )) {
        $connection->changeColumn(
                   $buyDictionaryCategoryTableName,
                       'category_id',
                       'native_id',
                       'varchar(255) DEFAULT NULL FIRST'
        );
    }

    if ($connection->tableColumnExists($buyDictionaryCategoryTableName, 'id') !== false &&
        $connection->tableColumnExists($buyDictionaryCategoryTableName, 'category_id') === false) {
        $connection->changeColumn(
                   $buyDictionaryCategoryTableName,
                       'id',
                       'category_id',
                       'int(11) UNSIGNED NOT NULL AFTER `node_id`');
    }

    if ($connection->tableColumnExists($buyDictionaryCategoryTableName, 'parent_id') !== false &&
        $connection->tableColumnExists($buyDictionaryCategoryTableName, 'parent_category_id') === false) {
        $connection->changeColumn(
                   $buyDictionaryCategoryTableName,
                       'parent_id',
                       'parent_category_id',
                       'int(11) UNSIGNED DEFAULT NULL'
        );
    }

    if ($connection->tableColumnExists($buyDictionaryCategoryTableName, 'id') === false) {
        $installer->run(<<<SQL
          ALTER TABLE `m2epro_buy_dictionary_category`
          ADD COLUMN `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
          DROP PRIMARY KEY,
          ADD PRIMARY KEY (`id`);
SQL
        );
    }

    $indexList = $connection->getIndexList($buyDictionaryCategoryTableName);

    $dropIndexes = array(
        'parent_id',
        'category_id'
    );

    foreach ($dropIndexes as $dropIndex) {
        if (isset($indexList[strtoupper($dropIndex)])) {
            $connection->dropKey($buyDictionaryCategoryTableName, $dropIndex);
        }
    }

    $newIndexes = array(
        'native_id',
        'category_id',
        'parent_category_id'
    );

    foreach ($newIndexes as $newIndex) {
        if (!isset($indexList[strtoupper($newIndex)])) {
            $connection->addKey($buyDictionaryCategoryTableName, $newIndex, $newIndex);
        }
    }
}

//#############################################

$installer->run(<<<SQL

    UPDATE `m2epro_ebay_marketplace`
    SET `is_global_shipping_program` = 1
    WHERE `marketplace_id` = 9;

SQL
);

//#############################################

$installer->endSetup();

//#############################################