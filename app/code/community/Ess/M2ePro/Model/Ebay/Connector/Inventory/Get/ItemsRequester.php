<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Ebay_Connector_Inventory_Get_ItemsRequester
    extends Ess_M2ePro_Model_Ebay_Connector_Command_Pending_Requester
{
    // ########################################

    public function getRequestData()
    {
        return array();
    }

    public function getCommand()
    {
        return array('inventory','get','items');
    }

    // ########################################

    protected function getResponserParams()
    {
        return array(
            'account_id'     => $this->account->getId(),
            'marketplace_id' => $this->marketplace->getId()
        );
    }

    // ########################################
}