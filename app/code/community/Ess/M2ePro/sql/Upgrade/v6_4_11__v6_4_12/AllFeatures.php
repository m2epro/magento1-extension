<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v6_4_11__v6_4_12_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->_installer;
        $connection = $installer->getConnection();

        // -- Remove Kill Now
        //########################################

        $installer->getTableModifier("lock_item")
            ->dropColumn("kill_now");

        // -- NewAmazonMarketplaces
        //########################################

        $tableName = $installer->getTablesObject()->getFullName('marketplace');
        $query = $connection->query("SELECT * FROM {$tableName} WHERE `id` IN (34, 35, 36)");
        $rows = $query->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {

            $installer->run(<<<SQL

INSERT INTO `m2epro_marketplace` VALUES
  (34, 9, 'Mexico', 'MX', 'amazon.com.mx', 0, 10, 'America', 'amazon', '2017-09-27 00:00:00', '2017-09-27 00:00:00'),
  (35, 10, 'Australia', 'AU', 'amazon.com.au', 0, 11, 'Asia / Pacific', 'amazon', '2017-09-27 00:00:00',
   '2017-09-27 00:00:00'),
  (36, 0, 'India', 'IN', 'amazon.in', 0, 12, 'Asia / Pacific', 'amazon', '2017-09-27 00:00:00', '2017-09-27 00:00:00');

INSERT INTO `m2epro_amazon_marketplace` VALUES
  (34, '8636-1433-4377', 'MXN', 0, 0),
  (35, '2770-5005-3793', 'AUD', 1, 0),
  (36, NULL, '', 0, 0);

SQL
            );
        }
    }

    //########################################
}