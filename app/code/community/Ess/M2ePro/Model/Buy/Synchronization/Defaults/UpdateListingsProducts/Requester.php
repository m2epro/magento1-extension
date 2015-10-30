<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Synchronization_Defaults_UpdateListingsProducts_Requester
    extends Ess_M2ePro_Model_Connector_Buy_Inventory_Get_ItemsRequester
{
    //########################################

    /**
     * @param Ess_M2ePro_Model_Processing_Request $processingRequest
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function setProcessingLocks(Ess_M2ePro_Model_Processing_Request $processingRequest)
    {
        parent::setProcessingLocks($processingRequest);

        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');

        $tempNick = Ess_M2ePro_Model_Buy_Synchronization_Defaults_UpdateListingsProducts::LOCK_ITEM_PREFIX;
        $tempNick .= '_'.$this->account->getId();

        $lockItem->setNick($tempNick);
        $lockItem->setMaxInactiveTime(Ess_M2ePro_Model_Processing_Request::MAX_LIFE_TIME_INTERVAL);

        $lockItem->create();

        $this->account->addObjectLock(NULL, $processingRequest->getHash());
        $this->account->addObjectLock('synchronization', $processingRequest->getHash());
        $this->account->addObjectLock('synchronization_buy', $processingRequest->getHash());
        $this->account->addObjectLock(
            Ess_M2ePro_Model_Buy_Synchronization_Defaults_UpdateListingsProducts::LOCK_ITEM_PREFIX,
            $processingRequest->getHash()
        );
    }

    //########################################

    protected function getResponserParams()
    {
        return array_merge(
            parent::getResponserParams(),
            array('request_date' => Mage::helper('M2ePro')->getCurrentGmtDate())
        );
    }

    //########################################
}