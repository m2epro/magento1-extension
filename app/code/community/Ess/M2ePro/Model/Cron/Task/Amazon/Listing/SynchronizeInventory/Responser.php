<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Amazon_Listing_SynchronizeInventory_Responser
    extends Ess_M2ePro_Model_Amazon_Connector_Inventory_Get_ItemsResponser
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
        $account = Mage::helper('M2ePro/Component_Amazon')->getObject('Account', $this->_params['account_id']);

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

    /**
     * @return bool
     */
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

    /**
     * @param string $messageText
     * @return void|null
     */
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
            $this->storeReceivedSkus();

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
     * @throws Zend_Db_Exception
     */
    protected function storeReceivedSkus()
    {
        $insertData = array();
        $accountId = $this->getAccount()->getId();

        foreach (array_keys($this->_preparedResponseData) as $sku) {
            $insertData[] = array('account_id' => $accountId, 'sku' => $sku);
        }

        Mage::getSingleton('core/resource')->getConnection('core_write')->insertOnDuplicate(
            Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('m2epro_amazon_inventory_sku'),
            $insertData
        );
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        if ($this->_account !== null) {
            return $this->_account;
        }

        return $this->_account = Mage::helper('M2ePro/Component_Amazon')->getObject(
            'Account',
            $this->_params['account_id']
        );
    }

    /**
     * @return Ess_M2ePro_Model_Synchronization_Log
     */
    protected function getSynchronizationLog()
    {
        if ($this->_synchronizationLog !== null) {
            return $this->_synchronizationLog;
        }

        $this->_synchronizationLog = Mage::getModel('M2ePro/Synchronization_Log');
        $this->_synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);
        $this->_synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_LISTINGS);

        return $this->_synchronizationLog;
    }

    /**
     * @return Ess_M2ePro_Model_Listing_SynchronizeInventory_Amazon_ListingProductsHandler
     */
    protected function getListingProductHandler()
    {
        return Mage::getModel('M2ePro/Listing_SynchronizeInventory_Amazon_ListingProductsHandler');
    }

    /**
     * @return Ess_M2ePro_Model_Listing_SynchronizeInventory_Amazon_OtherListingsHandler
     */
    protected function getOtherListingsHandler()
    {
        return Mage::getModel('M2ePro/Listing_SynchronizeInventory_Amazon_OtherListingsHandler');
    }

    //########################################
}
