<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Connector_Account_Add_EntityRequester
    extends Ess_M2ePro_Model_Walmart_Connector_Command_RealTime
{
    //########################################

    /**
     * @return array
     */
    public function getRequestData()
    {
        /** @var $marketplaceObject Ess_M2ePro_Model_Marketplace */
        $marketplaceObject = Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
            'Marketplace',
            $this->_params['marketplace_id']
        );

        if ($this->_params['marketplace_id'] == Ess_M2ePro_Helper_Component_Walmart::MARKETPLACE_CA) {
            $requestData = array(
                'title'          => $this->_account->getTitle(),
                'consumer_id'    => $this->_params['consumer_id'],
                'private_key'    => $this->_params['private_key'],
                'marketplace_id' => $marketplaceObject->getNativeId(),
            );
        } else {
            $requestData = array(
                'seller_id' => $this->_params['seller_id'],
                'auth_code' => $this->_params['auth_code'],
                'marketplace_id' => $marketplaceObject->getNativeId(),
            );
        }

        return $requestData;
    }

    /**
     * @return array
     */
    protected function getCommand()
    {
        return array('account', 'add', 'entity');
    }

    //########################################

    /**
     * @return bool
     */
    protected function validateResponse()
    {
        $responseData = $this->getResponse()->getData();
        if (empty($responseData['hash']) && !$this->getResponse()->getMessages()->hasErrorEntities()) {
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
