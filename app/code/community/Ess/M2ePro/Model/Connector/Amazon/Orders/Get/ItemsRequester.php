<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_Amazon_Orders_Get_ItemsRequester
    extends Ess_M2ePro_Model_Connector_Amazon_Requester
{
    //########################################

    /**
     * @return array
     */
    public function getCommand()
    {
        return array('orders','get','items');
    }

    //########################################

    protected function getResponserParams()
    {
        return array(
            'account_id' => $this->account->getId(),
            'marketplace_id' => $this->account->getChildObject()->getMarketplaceId()
        );
    }

    //########################################

    protected function getRequestData()
    {
        return array(
            'updated_since_time' => $this->params['from_date'],
            'status_filter' => !empty($this->params['status']) ? $this->params['status'] : NULL
        );
    }

    //########################################
}