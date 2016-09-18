<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_Amazon_Inventory_Get_ItemsRequester
    extends Ess_M2ePro_Model_Connector_Amazon_Requester
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

    protected function getResponserParams()
    {
        return array(
            'account_id' => $this->account->getId(),
        );
    }

    //########################################

    protected function getRequestData()
    {
        $requestData = array();
        if (isset($this->params['full_items_data'])) {
            $requestData['full_items_data'] = $this->params['full_items_data'];
        }

        return $requestData;
    }

    //########################################

    protected function getProcessingData()
    {
        return array_merge(
            parent::getProcessingData(),
            array('perform_type' => Ess_M2ePro_Model_Processing_Request::PERFORM_TYPE_PARTIAL)
        );
    }

    //########################################
}