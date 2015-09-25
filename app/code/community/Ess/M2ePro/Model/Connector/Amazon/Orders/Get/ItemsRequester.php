<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Amazon_Orders_Get_ItemsRequester
    extends Ess_M2ePro_Model_Connector_Amazon_Requester
{
    // ########################################

    public function getCommand()
    {
        return array('orders','get','items');
    }

    // ########################################

    protected function getResponserParams()
    {
        return array(
            'account_id' => $this->account->getId(),
            'marketplace_id' => $this->account->getChildObject()->getMarketplaceId()
        );
    }

    // ########################################

    protected function getRequestData()
    {
        return array(
            'updated_since_time' => $this->params['from_date'],
            'status_filter' => !empty($this->params['status']) ? $this->params['status'] : NULL
        );
    }

    // ########################################
}