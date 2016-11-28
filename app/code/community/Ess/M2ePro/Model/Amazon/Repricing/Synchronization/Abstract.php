<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
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
                $filters[$name] = json_encode($value);
            }

            $requestData['filters'] = $filters;
        }

        try {
            $result = $this->getHelper()->sendRequest(
                Ess_M2ePro_Helper_Component_Amazon_Repricing::COMMAND_SYNCHRONIZE,
                $requestData
            );
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
            return false;
        }

        return json_decode($result['response'], true);
    }

    //########################################
}