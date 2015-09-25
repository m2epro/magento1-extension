<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Ebay_Inventory_Get_ItemsResponser
    extends Ess_M2ePro_Model_Connector_Ebay_Responser
{
    // ########################################

    protected function validateResponseData($response)
    {
        if (!isset($response['items']) ||
            !isset($response['to_time'])) {
            return false;
        }

        return true;
    }

    // ########################################
}