<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Amazon_Connector_Inventory_Get_ItemsRequester
    extends Ess_M2ePro_Model_Amazon_Connector_Command_Pending_Requester
{
    // ########################################

    public function getRequestData()
    {
        $requestData = array();
        if (isset($this->_params['full_items_data'])) {
            $requestData['full_items_data'] = $this->_params['full_items_data'];
        }

        return $requestData;
    }

    public function getCommand()
    {
        return array('inventory','get','items');
    }

    // ########################################
}
