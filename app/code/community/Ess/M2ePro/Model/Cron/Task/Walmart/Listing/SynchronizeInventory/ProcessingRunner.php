<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Walmart_Listing_SynchronizeInventory_ProcessingRunner
    extends Ess_M2ePro_Model_Connector_Command_Pending_Processing_Partial_Runner
{
    const LOCK_ITEM_PREFIX = 'synchronization_walmart_listings_products_update';

    //##################################

    protected function setLocks()
    {
        parent::setLocks();

        $params = $this->getParams();

        /** @var $lockItemManager Ess_M2ePro_Model_Lock_Item_Manager */
        $lockItemManager = Mage::getModel(
            'M2ePro/Lock_Item_Manager', array('nick' => self::LOCK_ITEM_PREFIX)
        );
        $lockItemManager->create();

        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component_Walmart')->getCachedObject('Account', $params['account_id']);

        $account->addProcessingLock(null, $this->getProcessingObject()->getId());
        $account->addProcessingLock('synchronization', $this->getProcessingObject()->getId());
        $account->addProcessingLock('synchronization_walmart', $this->getProcessingObject()->getId());
        $account->addProcessingLock(self::LOCK_ITEM_PREFIX, $this->getProcessingObject()->getId());
    }

    protected function unsetLocks()
    {
        parent::unsetLocks();

        $params = $this->getParams();

        /** @var $lockItem Ess_M2ePro_Model_Lock_Item_Manager */
        $lockItem = Mage::getModel(
            'M2ePro/Lock_Item_Manager', array('nick' => self::LOCK_ITEM_PREFIX)
        );
        $lockItem->remove();

        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component_Walmart')->getCachedObject('Account', $params['account_id']);

        $account->deleteProcessingLocks(null, $this->getProcessingObject()->getId());
        $account->deleteProcessingLocks('synchronization', $this->getProcessingObject()->getId());
        $account->deleteProcessingLocks('synchronization_walmart', $this->getProcessingObject()->getId());
        $account->deleteProcessingLocks(self::LOCK_ITEM_PREFIX, $this->getProcessingObject()->getId());
    }
}