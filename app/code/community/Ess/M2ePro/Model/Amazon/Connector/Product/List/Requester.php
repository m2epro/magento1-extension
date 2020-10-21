<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Connector_Product_List_Requester
    extends Ess_M2ePro_Model_Amazon_Connector_Product_Requester
{
    protected $_generalValidatorObject = null;

    protected $_skuGeneralValidatorObject = null;

    protected $_skuSearchValidatorObject = null;

    protected $_skuExistenceValidatorObject = null;

    protected $_listTypeValidatorObject = null;

    protected $_validatorsData = array();

    //########################################

    protected function getProcessingRunnerModelName()
    {
        return 'Amazon_Connector_Product_List_ProcessingRunner';
    }

    //########################################

    public function getCommand()
    {
        return array('product','add','entities');
    }

    //########################################

    protected function getActionType()
    {
        return Ess_M2ePro_Model_Listing_Product::ACTION_LIST;
    }

    protected function getLogsAction()
    {
        return Ess_M2ePro_Model_Listing_Log::ACTION_LIST_PRODUCT_ON_COMPONENT;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product[] $listingProducts
     * @return Ess_M2ePro_Model_Listing_Product[]
     */
    protected function filterChildListingProductsByStatus(array $listingProducts)
    {
        $resultListingProducts = array();

        foreach ($listingProducts as $listingProduct) {
            if (!$listingProduct->isNotListed() || !$listingProduct->isListable()) {
                continue;
            }

            $resultListingProducts[] = $listingProduct;
        }

        return $resultListingProducts;
    }

    //########################################

    protected function validateListingProduct()
    {
        return $this->validateGeneralRequirements()
                && $this->validateSkuGeneralRequirements()
                && $this->validateSkuSearchRequirements()
                && $this->validateSkuExistenceRequirements()
                && $this->validateListTypeRequirements();
    }

    //########################################

    protected function validateGeneralRequirements()
    {
        $validator = $this->getGeneralValidatorObject();

        $validationResult = $validator->validate();

        foreach ($validator->getMessages() as $messageData) {
            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromPreparedData($messageData['text'], $messageData['type']);

            $this->storeLogMessage($message);
        }

        if ($validationResult) {
            $this->addValidatorsData($validator->getData());
            return true;
        }

        return false;
    }

    protected function validateSkuGeneralRequirements()
    {
        $validator = $this->getSkuGeneralValidatorObject();

        $validationResult = $validator->validate();

        foreach ($validator->getMessages() as $messageData) {
            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromPreparedData($messageData['text'], $messageData['type']);

            $this->storeLogMessage($message);
        }

        if ($validationResult) {
            $this->addValidatorsData($validator->getData());
            return true;
        }

        return false;
    }

    protected function validateSkuSearchRequirements()
    {
        $validator = $this->getSkuSearchValidatorObject();

        $validationResult = $validator->validate();

        foreach ($validator->getMessages() as $messageData) {
            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromPreparedData($messageData['text'], $messageData['type']);

            $this->storeLogMessage($message);
        }

        if ($validationResult) {
            $this->addValidatorsData($validator->getData());
            return true;
        }

        return false;
    }

    protected function validateSkuExistenceRequirements()
    {
        $sku = $this->getValidatorsData('sku');

        try {
            $countTriedTemp = 0;

            do {
                $countTriedTemp != 0 && sleep(3);

                /** @var $dispatcherObject Ess_M2ePro_Model_Amazon_Connector_Dispatcher */
                $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
                $connectorObj = $dispatcherObject->getVirtualConnector(
                    'product', 'search', 'asinBySkus',
                    array('include_info' => true,
                                                                             'only_realtime' => true,
                                                                             'items' => array($sku)),
                    'items',
                    $this->_account->getId()
                );
                $dispatcherObject->process($connectorObj);
                $response = $connectorObj->getResponseData();

                if ($response === null && $connectorObj->getResponse()->getMessages()->hasErrorEntities()) {
                    throw new Ess_M2ePro_Model_Exception(
                        $connectorObj->getResponse()->getMessages()->getCombinedErrorsString()
                    );
                }
            } while ($response === null && ++$countTriedTemp <= 3);

            if ($response === null) {
                throw new Ess_M2ePro_Model_Exception(
                    'Searching of SKU in your inventory on Amazon is not
                    available now. Please repeat the action later.'
                );
            }
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromPreparedData(
                Mage::helper('M2ePro')->__($exception->getMessage()),
                Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_ERROR
            );

            $this->storeLogMessage($message);

            return false;
        }

        $existenceResult = !empty($response[$sku]) ? $response[$sku] : array();

        $validator = $this->getSkuExistenceValidatorObject();
        $validator->setExistenceResult($existenceResult);

        $validationResult = $validator->validate();

        foreach ($validator->getMessages() as $messageData) {
            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromPreparedData($messageData['text'], $messageData['type']);

            $this->storeLogMessage($message);
        }

        if ($validationResult) {
            $this->addValidatorsData($validator->getData());
            return true;
        }

        return false;
    }

    protected function validateListTypeRequirements()
    {
        $validator = $this->getListTypeValidatorObject();

        $validationResult = $validator->validate();

        foreach ($validator->getMessages() as $messageData) {
            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromPreparedData($messageData['text'], $messageData['type']);

            $this->storeLogMessage($message);
        }

        if ($validationResult) {
            $this->addValidatorsData($validator->getData());
            return true;
        }

        return false;
    }

    //########################################

    protected function getValidatorsData($key = null)
    {
        if ($key === null) {
            return $this->_validatorsData;
        }

        return isset($this->_validatorsData[$key]) ? $this->_validatorsData[$key] : null;
    }

    protected function addValidatorsData(array $data)
    {
        $this->_validatorsData = array_merge($this->_validatorsData, $data);
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_General
     */
    protected function getGeneralValidatorObject()
    {
        if ($this->_generalValidatorObject === null) {

            /** @var $validator Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_General */
            $validator = Mage::getModel(
                'M2ePro/Amazon_Listing_Product_Action_Type_List_Validator_General'
            );

            $validator->setParams($this->_params);
            $validator->setListingProduct($this->_listingProduct);
            $validator->setData($this->getValidatorsData());
            $validator->setConfigurator($this->_listingProduct->getActionConfigurator());

            $this->_generalValidatorObject = $validator;
        }

        return $this->_generalValidatorObject;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_Sku_General
     */
    protected function getSkuGeneralValidatorObject()
    {
        if ($this->_skuGeneralValidatorObject === null) {

            /** @var $validator Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_Sku_General */
            $validator = Mage::getModel(
                'M2ePro/Amazon_Listing_Product_Action_Type_List_Validator_Sku_General'
            );

            $validator->setParams($this->_params);
            $validator->setListingProduct($this->_listingProduct);
            $validator->setData($this->getValidatorsData());
            $validator->setConfigurator($this->_listingProduct->getActionConfigurator());

            $this->_skuGeneralValidatorObject = $validator;
        }

        return $this->_skuGeneralValidatorObject;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_Sku_Search
     */
    protected function getSkuSearchValidatorObject()
    {
        if ($this->_skuSearchValidatorObject === null) {

            /** @var $validator Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_Sku_Search */
            $validator = Mage::getModel(
                'M2ePro/Amazon_Listing_Product_Action_Type_List_Validator_Sku_Search'
            );

            $validator->setParams($this->_params);
            $validator->setListingProduct($this->_listingProduct);
            $validator->setData($this->getValidatorsData());
            $validator->setConfigurator($this->_listingProduct->getActionConfigurator());

            $this->_skuSearchValidatorObject = $validator;
        }

        return $this->_skuSearchValidatorObject;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_Sku_Existence
     */
    protected function getSkuExistenceValidatorObject()
    {
        if ($this->_skuExistenceValidatorObject === null) {

            /** @var $validator Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_Sku_Existence */
            $validator = Mage::getModel(
                'M2ePro/Amazon_Listing_Product_Action_Type_List_Validator_Sku_Existence'
            );

            $validator->setParams($this->_params);
            $validator->setListingProduct($this->_listingProduct);
            $validator->setData($this->getValidatorsData());
            $validator->setConfigurator($this->_listingProduct->getActionConfigurator());

            $this->_skuExistenceValidatorObject = $validator;
        }

        return $this->_skuExistenceValidatorObject;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_ListType
     */
    protected function getListTypeValidatorObject()
    {
        if ($this->_listTypeValidatorObject === null) {

            /** @var $validator Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_ListType */
            $validator = Mage::getModel(
                'M2ePro/Amazon_Listing_Product_Action_Type_List_Validator_ListType'
            );

            $validator->setParams($this->_params);
            $validator->setListingProduct($this->_listingProduct);
            $validator->setData($this->getValidatorsData());
            $validator->setConfigurator($this->_listingProduct->getActionConfigurator());

            $this->_listTypeValidatorObject = $validator;
        }

        return $this->_listTypeValidatorObject;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Request
     */
    protected function getRequestObject()
    {
        if ($this->_requestObject === null) {
            /** @var $request Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Request */
            $request = Mage::getModel(
                'M2ePro/Amazon_Listing_Product_Action_Type_List_Request'
            );

            $request->setParams($this->_params);
            $request->setListingProduct($this->_listingProduct);
            $request->setConfigurator($this->_listingProduct->getActionConfigurator());
            $request->setCachedData($this->getValidatorsData());

            $this->_requestObject = $request;
        }

        return $this->_requestObject;
    }

    //########################################
}
