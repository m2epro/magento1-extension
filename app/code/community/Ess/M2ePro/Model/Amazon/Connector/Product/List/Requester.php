<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Connector_Product_List_Requester
    extends Ess_M2ePro_Model_Amazon_Connector_Product_Requester
{
    private $generalValidatorObject = NULL;

    private $skuGeneralValidatorObject = NULL;

    private $skuSearchValidatorObject = NULL;

    private $skuExistenceValidatorObject = NULL;

    private $listTypeValidatorObject = NULL;

    private $validatorsData = array();

    // ########################################

    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        parent::setListingProduct($listingProduct);

        $additionalData = $listingProduct->getAdditionalData();
        unset($additionalData['synch_template_list_rules_note']);
        $this->listingProduct->setSettings('additional_data', $additionalData);

        $this->listingProduct->save();

        return $this;
    }

    // ########################################

    protected function getProcessingRunnerModelName()
    {
        return 'Amazon_Connector_Product_List_ProcessingRunner';
    }

    // ########################################

    public function getCommand()
    {
        return array('product','add','entities');
    }

    // ########################################

    protected function getActionType()
    {
        return Ess_M2ePro_Model_Listing_Product::ACTION_LIST;
    }

    protected function getLogsAction()
    {
        return Ess_M2ePro_Model_Listing_Log::ACTION_LIST_PRODUCT_ON_COMPONENT;
    }

    // ########################################

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

    // ########################################

    protected function validateListingProduct()
    {
        return $this->validateGeneralRequirements()
                && $this->validateSkuGeneralRequirements()
                && $this->validateSkuSearchRequirements()
                && $this->validateSkuExistenceRequirements()
                && $this->validateListTypeRequirements();
    }

    // ########################################

    private function validateGeneralRequirements()
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

    private function validateSkuGeneralRequirements()
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

    private function validateSkuSearchRequirements()
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

    private function validateSkuExistenceRequirements()
    {
        $sku = $this->getValidatorsData('sku');

        try {

            $countTriedTemp = 0;

            do {

                $countTriedTemp != 0 && sleep(3);

                /** @var $dispatcherObject Ess_M2ePro_Model_Amazon_Connector_Dispatcher */
                $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
                $connectorObj = $dispatcherObject->getVirtualConnector('product','search','asinBySkus',
                                                                       array('include_info' => true,
                                                                             'only_realtime' => true,
                                                                             'items' => array($sku)),
                                                                       'items',
                                                                       $this->account->getId());
                $dispatcherObject->process($connectorObj);
                $response = $connectorObj->getResponseData();

                if (is_null($response) && $connectorObj->getResponse()->getMessages()->hasErrorEntities()) {
                    throw new Ess_M2ePro_Model_Exception(
                        $connectorObj->getResponse()->getMessages()->getCombinedErrorsString()
                    );
                }

            } while (is_null($response) && ++$countTriedTemp <= 3);

            if (is_null($response)) {
                throw new Ess_M2ePro_Model_Exception('Searching of SKU in your inventory on Amazon is not
                    available now. Please repeat the action later.');
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

    private function validateListTypeRequirements()
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

    // ########################################

    private function getValidatorsData($key = null)
    {
        if (is_null($key)) {
            return $this->validatorsData;
        }

        return isset($this->validatorsData[$key]) ? $this->validatorsData[$key] : null;
    }

    private function addValidatorsData(array $data)
    {
        $this->validatorsData = array_merge($this->validatorsData, $data);
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_General
     */
    private function getGeneralValidatorObject()
    {
        if (is_null($this->generalValidatorObject)) {

            /** @var $validator Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_General */
            $validator = Mage::getModel(
                'M2ePro/Amazon_Listing_Product_Action_Type_List_Validator_General'
            );

            $validator->setParams($this->params);
            $validator->setListingProduct($this->listingProduct);
            $validator->setData($this->getValidatorsData());
            $validator->setConfigurator($this->listingProduct->getActionConfigurator());

            $this->generalValidatorObject = $validator;
        }

        return $this->generalValidatorObject;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_Sku_General
     */
    private function getSkuGeneralValidatorObject()
    {
        if (is_null($this->skuGeneralValidatorObject)) {

            /** @var $validator Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_Sku_General */
            $validator = Mage::getModel(
                'M2ePro/Amazon_Listing_Product_Action_Type_List_Validator_Sku_General'
            );

            $validator->setParams($this->params);
            $validator->setListingProduct($this->listingProduct);
            $validator->setData($this->getValidatorsData());
            $validator->setConfigurator($this->listingProduct->getActionConfigurator());

            $this->skuGeneralValidatorObject = $validator;
        }

        return $this->skuGeneralValidatorObject;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_Sku_Search
     */
    private function getSkuSearchValidatorObject()
    {
        if (is_null($this->skuSearchValidatorObject)) {

            /** @var $validator Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_Sku_Search */
            $validator = Mage::getModel(
                'M2ePro/Amazon_Listing_Product_Action_Type_List_Validator_Sku_Search'
            );

            $validator->setParams($this->params);
            $validator->setListingProduct($this->listingProduct);
            $validator->setData($this->getValidatorsData());
            $validator->setConfigurator($this->listingProduct->getActionConfigurator());

            $this->skuSearchValidatorObject = $validator;
        }

        return $this->skuSearchValidatorObject;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_Sku_Existence
     */
    private function getSkuExistenceValidatorObject()
    {
        if (is_null($this->skuExistenceValidatorObject)) {

            /** @var $validator Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_Sku_Existence */
            $validator = Mage::getModel(
                'M2ePro/Amazon_Listing_Product_Action_Type_List_Validator_Sku_Existence'
            );

            $validator->setParams($this->params);
            $validator->setListingProduct($this->listingProduct);
            $validator->setData($this->getValidatorsData());
            $validator->setConfigurator($this->listingProduct->getActionConfigurator());

            $this->skuExistenceValidatorObject = $validator;
        }

        return $this->skuExistenceValidatorObject;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_ListType
     */
    private function getListTypeValidatorObject()
    {
        if (is_null($this->listTypeValidatorObject)) {

            /** @var $validator Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_ListType */
            $validator = Mage::getModel(
                'M2ePro/Amazon_Listing_Product_Action_Type_List_Validator_ListType'
            );

            $validator->setParams($this->params);
            $validator->setListingProduct($this->listingProduct);
            $validator->setData($this->getValidatorsData());
            $validator->setConfigurator($this->listingProduct->getActionConfigurator());

            $this->listTypeValidatorObject = $validator;
        }

        return $this->listTypeValidatorObject;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Request
     */
    protected function getRequestObject()
    {
        if (is_null($this->requestObject)) {

            /* @var $request Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Request */
            $request = Mage::getModel(
                'M2ePro/Amazon_Listing_Product_Action_Type_List_Request'
            );

            $request->setParams($this->params);
            $request->setListingProduct($this->listingProduct);
            $request->setConfigurator($this->listingProduct->getActionConfigurator());
            $request->setCachedData($this->getValidatorsData());

            $this->requestObject = $request;
        }

        return $this->requestObject;
    }

    // ########################################
}