<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_Ebay_Inventory_Get_ItemsResponser
    extends Ess_M2ePro_Model_Connector_Ebay_Responser
{
    //########################################

    protected function validateResponseData($response)
    {
        if (!isset($response['items']) ||
            !isset($response['to_time'])) {
            return false;
        }

        return true;
    }

    //########################################
}