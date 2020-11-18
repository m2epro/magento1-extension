<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Connector_Account_Update_EntityRequester
    extends Ess_M2ePro_Model_Walmart_Connector_Command_Pending_Requester
{
    //########################################

    public function getRequestData()
    {
        /** @var Ess_M2ePro_Model_Marketplace $marketplaceObject */
        $marketplaceObject = Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
            'Marketplace', $this->_params['marketplace_id']
        );

        $this->_params['marketplace_id'] = $marketplaceObject->getNativeId();

        return $this->_params;
    }

    protected function getCommand()
    {
        return array('account','update','entity');
    }

    //########################################

    protected function getProcessingRunnerModelName()
    {
        return 'Walmart_Connector_Account_Update_ProcessingRunner';
    }

    //########################################
}
