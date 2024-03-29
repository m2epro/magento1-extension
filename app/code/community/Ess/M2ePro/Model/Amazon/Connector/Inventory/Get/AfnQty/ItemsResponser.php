<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Amazon_Connector_Inventory_Get_AfnQty_ItemsResponser
    extends Ess_M2ePro_Model_Connector_Command_Pending_Responser
{
    protected function validateResponse()
    {
        $responseData = $this->getResponse()->getData();
        return isset($responseData['data']);
    }
}
