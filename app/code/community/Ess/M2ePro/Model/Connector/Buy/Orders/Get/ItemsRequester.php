<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_Buy_Orders_Get_ItemsRequester
    extends Ess_M2ePro_Model_Connector_Buy_Requester
{
    //########################################

    public function getCommand()
    {
        return array('orders','get','items');
    }

    //########################################

    protected function getResponserParams()
    {
        return array(
            'account_id' => $this->account->getId(),
        );
    }

    //########################################

    protected function getRequestData()
    {
        return array();
    }

    //########################################
}