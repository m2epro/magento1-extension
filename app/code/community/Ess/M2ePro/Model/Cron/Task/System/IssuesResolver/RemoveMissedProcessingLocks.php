<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_System_IssuesResolver_RemoveMissedProcessingLocks
    extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'system/issues_resolver/remove_missed_processing_locks';

    //########################################

    protected function performActions()
    {
        /** @var Ess_M2ePro_Model_Resource_Processing_Lock_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Processing_Lock_Collection');
        $collection->getSelect()->joinLeft(
            array('p' => Mage::getResourceModel('M2ePro/Processing')->getMainTable()),
            'p.id = main_table.processing_id',
            array()
        );
        $collection->addFieldToFilter('p.id', array('null' => true));

        $logData = array();
        foreach ($collection->getItems() as $item) {
            /**@var Ess_M2ePro_Model_Processing_Lock $item */

            if (!isset($logData[$item->getModelName()][$item->getObjectId()]) ||
                !in_array($item->getTag(), $logData[$item->getModelName()][$item->getObjectId()]))
            {
                $logData[$item->getModelName()][$item->getObjectId()][] = $item->getTag();
            }

            $item->deleteInstance();
        }

        if (!empty($logData)) {
            Mage::helper('M2ePro/Module_Logger')->process(
                $logData, 'Processing Locks Records were broken and removed', false
            );
        }
    }

    //########################################
}
