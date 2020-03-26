<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y19_m10_SynchronizationConfigsModeRemoved
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    /**
     * @throws Ess_M2ePro_Model_Exception_Setup
     * @throws Zend_Db_Statement_Exception
     */
    public function execute()
    {
        $this->_installer->getMainConfigModifier()->delete('/cron/checker/task/repair_crashed_tables/');

        $connection = $this->_installer->getConnection();
        $configTable = $this->_installer->getFullTableName('config');
        $groupsToSkip = array(
            '/cron/task/system/servicing/synchronize/' => 'interval',
            '/cron/task/ebay/listing/product/process_instructions/' => 'mode',
            '/cron/task/amazon/listing/product/process_instructions/' => 'mode',
            '/cron/task/walmart/listing/product/process_instructions/' => 'mode'
        );

        $query = $connection->select()
            ->from($configTable)
            ->where('`key` IN (?)', array('last_access', 'last_run', 'interval', 'mode'))
            ->where('`group` LIKE ?', '/cron/task/%')
            ->query();

        $ids = array();
        while ($row = $query->fetch()) {

            if (isset($groupsToSkip[$row['group']]) && $groupsToSkip[$row['group']] == $row['key']) {
                continue;
            }

            $ids[] = $row['id'];
        }

        $connection->delete($configTable, array('`id` IN (?)' => $ids));
    }

    //########################################
}
