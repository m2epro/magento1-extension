<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Ebay_Connector_Inventory_Get_ItemsResponser
    extends Ess_M2ePro_Model_Ebay_Connector_Command_Pending_Responser
{
    // ########################################

    protected function validateResponse()
    {
        $responseData = $this->getResponse()->getData();
        if (!isset($responseData['items']) || !isset($responseData['to_time'])) {
            return false;
        }

        return true;
    }

    // ########################################
}
