<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Connector_Account_Add_EntityRequester
    extends Ess_M2ePro_Model_Amazon_Connector_Command_RealTime
{
    //########################################

    /**
     * @return array
     */
    public function getRequestData()
    {
        /** @var $marketplaceObject Ess_M2ePro_Model_Marketplace */
        $marketplaceObject = Mage::helper('M2ePro/Component_Amazon')->getCachedObject(
            'Marketplace', $this->_params['marketplace_id']
        );

        return array(
            'title'          => $this->_account->getTitle(),
            'merchant_id'    => $this->_params['merchant_id'],
            'token'          => $this->_params['token'],
            'marketplace_id' => $marketplaceObject->getNativeId(),
        );
    }

    /**
     * @return array
     */
    protected function getCommand()
    {
        return array('account','add','entity');
    }

    //########################################

    /**
     * @return bool
     */
    protected function validateResponse()
    {
        $responseData = $this->getResponse()->getData();
        if ((empty($responseData['hash']) || !isset($responseData['info'])) &&
            !$this->getResponse()->getMessages()->hasErrorEntities()
        ) {
            return false;
        }

        return true;
    }

    /**
     * @throws Exception
     */
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
