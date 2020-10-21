<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Walmart_Connector_Inventory_Get_ItemsResponser
    extends Ess_M2ePro_Model_Connector_Command_Pending_Responser
{
    //########################################

    protected function validateResponse()
    {
        $responseData = $this->getResponse()->getData();
        return isset($responseData['data']);
    }

    protected function prepareResponseData()
    {
        $preparedData = array();

        $responseData = $this->getResponse()->getData();

        foreach ($responseData['data'] as $receivedItem) {
            if (empty($receivedItem['wpid'])) {
                continue;
            }

            $preparedData[$receivedItem['wpid']] = $receivedItem;
        }

        $this->_preparedResponseData = $preparedData;
    }

    //########################################
}
