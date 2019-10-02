<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Amazon_Connector_Orders_Get_Details_ItemsRequester
    extends Ess_M2ePro_Model_Amazon_Connector_Command_Pending_Requester
{
    // ########################################

    public function getCommand()
    {
        return array('orders','get','entitiesDetails');
    }

    // ########################################

    protected function getProcessingRunnerModelName()
    {
        return 'Connector_Command_Pending_Processing_Partial_Runner';
    }

    // ########################################

    protected function getResponserParams()
    {
        return array(
            'account_id'     => $this->_account->getId(),
            'marketplace_id' => $this->_account->getChildObject()->getMarketplaceId()
        );
    }

    // ########################################

    public function getRequestData()
    {
        return array(
            'items' => $this->_params['items'],
        );
    }

    // ########################################
}
