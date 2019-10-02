<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Amazon_Repricing_Synchronization_Abstract
    extends Ess_M2ePro_Model_Amazon_Repricing_Abstract
{
    const MODE_GENERAL      = 'general';
    const MODE_ACTUAL_PRICE = 'actual_price';

    //########################################

    abstract public function run($skus = NULL);

    //########################################

    abstract protected function getMode();

    //########################################

    protected function sendRequest(array $filters = array())
    {
        $requestData = array(
            'account_token' => $this->getAmazonAccountRepricing()->getToken(),
            'mode'          => $this->getMode()
        );

        if (!empty($filters)) {
            foreach ($filters as $name => $value) {
                $filters[$name] = Mage::helper('M2ePro')->jsonEncode($value);
            }

            $requestData['filters'] = $filters;
        }

        try {
            $result = $this->getHelper()->sendRequest(
                Ess_M2ePro_Helper_Component_Amazon_Repricing::COMMAND_SYNCHRONIZE,
                $requestData
            );
        } catch (Exception $exception) {
            $this->getSynchronizationLog()->addMessage(
                Mage::helper('M2ePro')->__($exception->getMessage()),
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );

            Mage::helper('M2ePro/Module_Exception')->process($exception, false);
            return false;
        }

        $this->processErrorMessages($result['response']);
        return $result['response'];
    }

    //########################################
}
