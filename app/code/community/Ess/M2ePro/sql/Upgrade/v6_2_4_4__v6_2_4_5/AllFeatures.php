<?php

class Ess_M2ePro_Sql_Upgrade_v6_2_4_4__v6_2_4_5_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->installer;
        $connection = $installer->getConnection();

        $tempTable = $installer->getTable('m2epro_product_change');

        if ($connection->tableColumnExists($tempTable, 'initiators') === false &&
            $connection->tableColumnExists($tempTable, 'creator_type') !== false) {
            $connection->changeColumn($tempTable, 'creator_type', 'initiators', 'VARCHAR(16) NOT NULL');
        }

        $tempTableIndexList = $connection->getIndexList($tempTable);

        if (isset($tempTableIndexList[strtoupper('creator_type')])) {
            $connection->dropKey($tempTable, 'creator_type');
        }

        if (!isset($tempTableIndexList[strtoupper('initiators')])) {
            $connection->addKey($tempTable, 'initiators', 'initiators');
        }
    }

    //########################################
}