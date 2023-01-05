<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Repricing_Abstract
{
    /** @var Ess_M2ePro_Model_Account $_account */
    protected $_account = null;

    /** @var Ess_M2ePro_Model_Synchronization_Log $_synchronizationLog */
    protected $_synchronizationLog = null;

    //########################################

    public function __construct(Ess_M2ePro_Model_Account $account)
    {
        $this->_account = $account;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->_account;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Account
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getAmazonAccount()
    {
        return $this->getAccount()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Account_Repricing
     */
    protected function getAmazonAccountRepricing()
    {
        return $this->getAmazonAccount()->getRepricing();
    }

    /**
     * @return Ess_M2ePro_Helper_Component_Amazon_Repricing
     */
    protected function getHelper()
    {
        return Mage::helper('M2ePro/Component_Amazon_Repricing');
    }

    //########################################

    protected function processErrorMessages($response)
    {
        if (empty($response['messages'])) {
            return;
        }

        foreach ($response['messages'] as $messageData) {
            $message = new Ess_M2ePro_Model_Amazon_Repricing_ResponseMessage(
                isset($messageData['text']) ? $messageData['text'] : '',
                isset($messageData['type'])
                    ? $messageData['type']
                    : Ess_M2ePro_Model_Amazon_Repricing_ResponseMessage::TYPE_WARNING,
                isset($messageData['code'])
                    ? (int)$messageData['code']
                    : Ess_M2ePro_Model_Amazon_Repricing_ResponseMessage::DEFAULT_CODE
            );

            if (!$message->isError()) {
                continue;
            }

            $errorText = $message->getText();

            if ($message->getCode() === Ess_M2ePro_Model_Amazon_Repricing_ResponseMessage::NOT_FOUND_ACCOUNT_CODE) {
                $errorText = 'Repricer account is invalid.';

                if (!$this->getAmazonAccountRepricing()->isInvalid()) {
                    $this->getAmazonAccountRepricing()
                        ->markAsInvalid()
                        ->save();

                    /** @var Ess_M2ePro_Helper_Data_Cache_Permanent $cache */
                    $cache = Mage::helper('M2ePro/Data_Cache_Permanent');
                    $cache->removeValue(Ess_M2ePro_Model_Amazon_Repricing_Issue_InvalidToken::CACHE_KEY);
                }
            }

            $this->getSynchronizationLog()->addMessage(
                Mage::helper('M2ePro')->__($errorText),
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
            );

            $exception = new Exception($errorText);
            Mage::helper('M2ePro/Module_Exception')->process($exception);
        }
    }

    //########################################

    protected function getSynchronizationLog()
    {
        if ($this->_synchronizationLog !== null) {
            return $this->_synchronizationLog;
        }

        $this->_synchronizationLog = Mage::getModel('M2ePro/Synchronization_Log');
        $this->_synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);
        $this->_synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_REPRICING);

        return $this->_synchronizationLog;
    }

    //########################################
}
