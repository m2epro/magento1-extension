<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Synchronization_Defaults_UpdateListingsProducts_Requester
    extends Ess_M2ePro_Model_Connector_Amazon_Inventory_Get_ItemsRequester
{
    // ########################################

    public function setProcessingLocks(Ess_M2ePro_Model_Processing_Request $processingRequest)
    {
        parent::setProcessingLocks($processingRequest);

        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');

        $tempNick = Ess_M2ePro_Model_Amazon_Synchronization_Defaults_UpdateListingsProducts::LOCK_ITEM_PREFIX;
        $tempNick .= '_'.$this->account->getId();

        $lockItem->setNick($tempNick);
        $lockItem->setMaxInactiveTime(Ess_M2ePro_Model_Processing_Request::MAX_LIFE_TIME_INTERVAL);

        $lockItem->create();

        $this->account->addObjectLock(NULL, $processingRequest->getHash());
        $this->account->addObjectLock('synchronization', $processingRequest->getHash());
        $this->account->addObjectLock('synchronization_amazon', $processingRequest->getHash());
        $this->account->addObjectLock(
            Ess_M2ePro_Model_Amazon_Synchronization_Defaults_UpdateListingsProducts::LOCK_ITEM_PREFIX,
            $processingRequest->getHash()
        );
    }

    // ########################################

    protected function getResponserParams()
    {
        return array_merge(
            parent::getResponserParams(),
            array(
                'processed_inventory_hash' => Mage::helper('M2ePro')->generateUniqueHash(),
                'request_date'             => Mage::helper('M2ePro')->getCurrentGmtDate(),
            )
        );
    }

    // ########################################
}