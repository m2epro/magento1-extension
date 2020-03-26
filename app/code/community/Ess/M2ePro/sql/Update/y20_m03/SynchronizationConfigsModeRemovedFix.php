<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m03_SynchronizationConfigsModeRemovedFix
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    /**
     * @throws Ess_M2ePro_Model_Exception_Setup
     * @throws Zend_Db_Statement_Exception
     */
    public function execute()
    {
        $groupsToFix = array(
            '/cron/task/ebay/listing/product/process_instructions/' => 'mode',
            '/cron/task/amazon/listing/product/process_instructions/' => 'mode',
            '/cron/task/walmart/listing/product/process_instructions/' => 'mode'
        );

        foreach ($groupsToFix as $group => $key) {
            $entity = $this->_installer->getMainConfigModifier()->getEntity($group, $key);
            if (!$entity->isExists()) {
                $entity->insert('1');
            }
        }
    }

    //########################################
}
