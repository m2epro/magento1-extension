<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Walmart_Listing_SynchronizeInventory_Responser
    extends Ess_M2ePro_Model_Walmart_Connector_Inventory_Get_ItemsResponser
{
    const INSTRUCTION_INITIATOR = 'channel_changes_synchronization';

    /** @var Ess_M2ePro_Model_Synchronization_Log */
    protected $_synchronizationLog;

    /** @var Ess_M2ePro_Model_Account */
    protected $_account;

    //########################################

    protected function processResponseMessages()
    {
        parent::processResponseMessages();

        foreach ($this->getResponse()->getMessages()->getEntities() as $message) {
            if (!$message->isError() && !$message->isWarning()) {
                continue;
            }

            $logType = $message->isError() ? Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
                : Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;

            $this->getSynchronizationLog()->addMessage(
                Mage::helper('M2ePro')->__($message->getText()),
                $logType
            );
        }
    }

    public function eventAfterExecuting()
    {
        parent::eventAfterExecuting();

        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component_Walmart')->getObject('Account', $this->_params['account_id']);

        /** @var Ess_M2ePro_Helper_Data $helper */
        $helper = Mage::helper('M2ePro');
        $newSynchDate = $helper->getCurrentGmtDate();

        if ($this->getResponse()->getMessages() && $this->getResponse()->getMessages()->hasErrorEntities()) {
            $newSynchTimestamp = (int)$helper->createGmtDateTime($newSynchDate)
                ->format('U');

            //try download inventory again in an hour
            $newSynchDate = date('Y-m-d H:i:s', $newSynchTimestamp + 3600);
        }

        $account->setData('inventory_last_synchronization', $newSynchDate)->save();
    }

    protected function isNeedProcessResponse()
    {
        if (!parent::isNeedProcessResponse()) {
            return false;
        }

        if ($this->getResponse()->getMessages()->hasErrorEntities()) {
            return false;
        }

        return true;
    }

    //########################################

    public function failDetected($messageText)
    {
        parent::failDetected($messageText);

        $this->getSynchronizationLog()->addMessage(
            Mage::helper('M2ePro')->__($messageText),
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
        );
    }

    //########################################

    protected function processResponseData()
    {
        try {
            $filteredData = $this->getListingProductHandler()
                                 ->setResponserParams($this->_params)
                                 ->handle($this->getPreparedResponseData());

            if ($this->getAccount()->getChildObject()->getOtherListingsSynchronization()) {
                $this->getOtherListingsHandler()->setResponserParams($this->_params)->handle($filteredData);
            }
        } catch (Exception $e) {
            Mage::helper('M2ePro/Module_Exception')->process($e);
            $this->getSynchronizationLog()->addMessageFromException($e);
        }
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        if ($this->_account === null) {
            $this->_account = Mage::helper('M2ePro/Component_Walmart')->getObject(
                'Account',
                $this->_params['account_id']
            );
        }

        return $this->_account;
    }

    protected function getSynchronizationLog()
    {
        if ($this->_synchronizationLog !== null) {
            return $this->_synchronizationLog;
        }

        $this->_synchronizationLog = Mage::getModel('M2ePro/Synchronization_Log');
        $this->_synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Walmart::NICK);
        $this->_synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_LISTINGS);

        return $this->_synchronizationLog;
    }

    /**
     * @return Ess_M2ePro_Model_Listing_SynchronizeInventory_Walmart_ListingProductsHandler
     */
    protected function getListingProductHandler()
    {
        return Mage::getModel('M2ePro/Listing_SynchronizeInventory_Walmart_ListingProductsHandler');
    }

    /**
     * @return Ess_M2ePro_Model_Listing_SynchronizeInventory_Walmart_OtherListingsHandler
     */
    protected function getOtherListingsHandler()
    {
        return Mage::getModel('M2ePro/Listing_SynchronizeInventory_Walmart_OtherListingsHandler');
    }

    //########################################
}
