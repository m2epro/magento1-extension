<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_Buy_Inventory_Get_ItemsRequester
    extends Ess_M2ePro_Model_Connector_Buy_Requester
{
    //########################################

    /**
     * @return array
     */
    public function getCommand()
    {
        return array('inventory','get','items');
    }

    //########################################

    /**
     * @return array
     */
    protected function getResponserParams()
    {
        return array(
            'account_id' => $this->account->getId(),
        );
    }

    //########################################

    /**
     * @return array
     */
    protected function getRequestData()
    {
        return array();
    }

    //########################################

    /**
     * @return array
     */
    protected function getProcessingData()
    {
        return array_merge(
            parent::getProcessingData(),
            array('perform_type' => Ess_M2ePro_Model_Processing_Request::PERFORM_TYPE_PARTIAL)
        );
    }

    //########################################
}