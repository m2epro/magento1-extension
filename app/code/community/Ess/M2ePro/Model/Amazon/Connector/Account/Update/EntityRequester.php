<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Connector_Account_Update_EntityRequester
    extends Ess_M2ePro_Model_Amazon_Connector_Command_RealTime
{
    //########################################

    public function getRequestData()
    {
        /** @var $marketplaceObject Ess_M2ePro_Model_Marketplace */
        $marketplaceObject = Mage::helper('M2ePro/Component_Amazon')->getCachedObject(
            'Marketplace', $this->_params['marketplace_id']
        );

        return array(
            'merchant_id'    => $this->_params['merchant_id'],
            'token'          => $this->_params['token'],
            'marketplace_id' => $marketplaceObject->getNativeId(),
        );
    }

    protected function getCommand()
    {
        return array('account','update','entity');
    }

    protected function validateResponse()
    {
        if (!parent::validateResponse()) {
            return false;
        }

        $responseData = $this->getResponse()->getData();
        if (!isset($responseData['info']) && !$this->getResponse()->getMessages()->hasErrorEntities()) {
            throw new Exception('Validation Failed. The Server response data is not valid.');
        }

        return true;
    }

    protected function prepareResponseData()
    {
        foreach ($this->getResponse()->getMessages()->getEntities() as $message) {
            if (!$message->isError()) {
                continue;
            }

            throw new Exception($message->getText());
        }

        $this->_responseData = $this->getResponse()->getData();
    }

    //########################################
}
