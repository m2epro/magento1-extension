<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Connector_Product_List_UpdateInventory_Responser
    extends Ess_M2ePro_Model_Walmart_Connector_Product_Responser
{
    /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Action_ProcessingList */
    protected $_processingList;

    //########################################

    protected function processSuccess(array $params = array())
    {
        $this->getResponseObject()->processSuccess($params);
        $this->_isSuccess = true;
    }

    protected function getSuccessfulMessage()
    {
        return null;
    }

    //########################################

    protected function getResponseObject()
    {
        $responseObject = parent::getResponseObject();
        $responseObject->setRequestMetaData(array());

        return $responseObject;
    }

    //########################################

    protected function getOrmActionType()
    {
        switch ($this->getActionType()) {
            case Ess_M2ePro_Model_Listing_Product::ACTION_LIST:
                return 'List_UpdateInventory';
        }

        throw new Ess_M2ePro_Model_Exception('Wrong Action type');
    }

    // ---------------------------------------

    protected function getRequestDataObject()
    {
        $requestObject = parent::getRequestDataObject();
        $requestObject->setData($this->_processingList->getRelistRequestData());

        return $requestObject;
    }

    protected function getConfigurator()
    {
        $configurator = parent::getConfigurator();
        $configurator->setData($this->_processingList->getRelistConfiguratorData());

        return $configurator;
    }

    //########################################

    public function setProcessingList(Ess_M2ePro_Model_Walmart_Listing_Product_Action_ProcessingList $processingList)
    {
        $this->_processingList = $processingList;
        return $this;
    }

    //########################################
}
