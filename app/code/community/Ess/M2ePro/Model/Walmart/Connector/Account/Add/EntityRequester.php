<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Connector_Account_Add_EntityRequester
    extends Ess_M2ePro_Model_Walmart_Connector_Command_Pending_Requester
{
    //########################################

    public function getRequestData()
    {
        /** @var $marketplaceObject Ess_M2ePro_Model_Marketplace */
        $marketplaceObject = Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
            'Marketplace', $this->params['marketplace_id']
        );

        if ($this->params['marketplace_id'] == Ess_M2ePro_Helper_Component_Walmart::MARKETPLACE_CA) {
            $requestData = array(
                'title'          => $this->account->getTitle(),
                'consumer_id'    => $this->params['consumer_id'],
                'private_key'    => $this->params['private_key'],
                'marketplace_id' => $marketplaceObject->getNativeId(),
            );
        } else {
            $requestData = array(
                'title'          => $this->account->getTitle(),
                'consumer_id'    => $this->params['consumer_id'],
                'client_id'      => $this->params['client_id'],
                'client_secret'  => $this->params['client_secret'],
                'marketplace_id' => $marketplaceObject->getNativeId(),
            );
        }

        return $requestData;
    }

    protected function getCommand()
    {
        return array('account','add','entity');
    }

    //########################################

    protected function getProcessingRunnerModelName()
    {
        return 'Walmart_Connector_Account_Add_ProcessingRunner';
    }

    //########################################
}