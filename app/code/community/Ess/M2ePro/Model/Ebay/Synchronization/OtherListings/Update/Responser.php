<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Update_Responser
    extends Ess_M2ePro_Model_Connector_Ebay_Inventory_Get_ItemsResponser
{
    protected $synchronizationLog = NULL;

    //########################################

    protected function processResponseMessages(array $messages = array())
    {
        parent::processResponseMessages($messages);

        foreach ($this->messages as $message) {

            if (!$this->isMessageError($message) && !$this->isMessageWarning($message)) {
                continue;
            }

            $logType = $this->isMessageError($message) ? Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
                                                       : Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;

            $this->getSynchronizationLog()->addMessage(
                Mage::helper('M2ePro')->__($message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY]),
                $logType,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );
        }
    }

    protected function isNeedToParseResponseData($responseBody)
    {
        if (!parent::isNeedToParseResponseData($responseBody)) {
            return false;
        }

        if ($this->hasErrorMessages()) {
            return false;
        }

        return true;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Processing_Request $processingRequest
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function unsetProcessingLocks(Ess_M2ePro_Model_Processing_Request $processingRequest)
    {
        parent::unsetProcessingLocks($processingRequest);

        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');

        $tempNick = Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Update::LOCK_ITEM_PREFIX;
        $tempNick .= '_'.$this->params['account_id'];

        $lockItem->setNick($tempNick);
        $lockItem->setMaxInactiveTime(Ess_M2ePro_Model_Processing_Request::MAX_LIFE_TIME_INTERVAL);

        $lockItem->remove();

        $this->getAccount()->deleteObjectLocks(NULL, $processingRequest->getHash());
        $this->getAccount()->deleteObjectLocks('synchronization', $processingRequest->getHash());
        $this->getAccount()->deleteObjectLocks('synchronization_ebay', $processingRequest->getHash());
        $this->getAccount()->deleteObjectLocks(
            Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Update::LOCK_ITEM_PREFIX,
            $processingRequest->getHash()
        );
    }

    public function eventFailedExecuting($message)
    {
        parent::eventFailedExecuting($message);

        $this->getSynchronizationLog()->addMessage(
            Mage::helper('M2ePro')->__($message),
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
        );
    }

    //########################################

    protected function processResponseData($response)
    {
        try {

            /** @var $updatingModel Ess_M2ePro_Model_Ebay_Listing_Other_Updating */
            $updatingModel = Mage::getModel('M2ePro/Ebay_Listing_Other_Updating');
            $updatingModel->initialize($this->getAccount());
            $updatingModel->processResponseData($response);

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $this->getSynchronizationLog()->addMessage(Mage::helper('M2ePro')->__($exception->getMessage()),
                                                       Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                                                       Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH);
        }
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getObjectByParam('Account','account_id');
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return $this->getObjectByParam('Marketplace','marketplace_id');
    }

    // ---------------------------------------

    protected function getSynchronizationLog()
    {
        if (!is_null($this->synchronizationLog)) {
            return $this->synchronizationLog;
        }

        $this->synchronizationLog = Mage::getModel('M2ePro/Synchronization_Log');
        $this->synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);
        $this->synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_OTHER_LISTINGS);

        return $this->synchronizationLog;
    }

    //########################################
}