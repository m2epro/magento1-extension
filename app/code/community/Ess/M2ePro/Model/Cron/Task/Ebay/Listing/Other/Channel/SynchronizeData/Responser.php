<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Ebay_Listing_Other_Channel_SynchronizeData_Responser
    extends Ess_M2ePro_Model_Ebay_Connector_Inventory_Get_ItemsResponser
{
    protected $_synchronizationLog = null;

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

            /** @var $updatingModel Ess_M2ePro_Model_Ebay_Listing_Other_Updating */
            $updatingModel = Mage::getModel('M2ePro/Ebay_Listing_Other_Updating');
            $updatingModel->initialize(
                Mage::helper('M2ePro/Component_Ebay')->getObject('Account', $this->_params['account_id'])
            );
            $updatingModel->processResponseData($this->getPreparedResponseData());
        } catch (Exception $e) {
            Mage::helper('M2ePro/Module_Exception')->process($e);
            $this->getSynchronizationLog()->addMessageFromException($e);
        }
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Synchronization_Log
     */
    protected function getSynchronizationLog()
    {
        if ($this->_synchronizationLog !== null) {
            return $this->_synchronizationLog;
        }

        $this->_synchronizationLog = Mage::getModel('M2ePro/Synchronization_Log');
        $this->_synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);
        $this->_synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_OTHER_LISTINGS);

        return $this->_synchronizationLog;
    }

    //########################################
}
