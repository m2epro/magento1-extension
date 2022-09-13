<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Amazon_Connector_Inventory_Get_AfnQty_ItemsRequester
    extends Ess_M2ePro_Model_Amazon_Connector_Command_Pending_Requester
{
    public function getRequestData()
    {
        return array();
    }

    public function getCommand()
    {
        return array('inventory', 'get', 'afnQty');
    }
}
