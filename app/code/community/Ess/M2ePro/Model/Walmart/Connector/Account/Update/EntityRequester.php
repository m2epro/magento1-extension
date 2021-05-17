<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Connector_Account_Update_EntityRequester
    extends Ess_M2ePro_Model_Walmart_Connector_Command_RealTime
{
    //########################################

    /**
     * @return array
     */
    public function getRequestData()
    {
        /** @var Ess_M2ePro_Model_Marketplace $marketplaceObject */
        $marketplaceObject = Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
            'Marketplace', $this->_params['marketplace_id']
        );

        $this->_params['marketplace_id'] = $marketplaceObject->getNativeId();

        return $this->_params;
    }

    /**
     * @return array
     */
    protected function getCommand()
    {
        return array('account','update','entity');
    }

    //########################################

    /**
     * @return bool
     */
    protected function validateResponse()
    {
        $responseData = $this->getResponse()->getData();
        if (!isset($responseData['info']) && !$this->getResponse()->getMessages()->hasErrorEntities()) {
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
