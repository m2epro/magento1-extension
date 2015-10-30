<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Amazon_Product_List_MultipleRequester
    extends Ess_M2ePro_Model_Connector_Amazon_Product_Requester
{
    private $generalValidatorsObjects = array();

    private $skuGeneralValidatorsObjects = array();

    private $skuSearchValidatorsObjects = array();

    private $skuExistenceValidatorsObjects = array();

    private $listTypeValidatorsObjects = array();

    private $validatorsData = array();

    //########################################

    public function __construct(array $params = array(), array $listingsProducts)
    {
        parent::__construct($params, $listingsProducts);

        foreach ($this->listingsProducts as $listingProduct) {
            $listingProduct->setData('synch_status', Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_OK);
            $listingProduct->setData('synch_reasons', null);

            $additionalData = $listingProduct->getAdditionalData();
            unset($additionalData['synch_template_list_rules_note']);
            $listingProduct->setSettings('additional_data', $additionalData);

            $listingProduct->save();
        }
    }

    //########################################

    /**
     * @return array
     */
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

    public function eventBeforeProcessing()
    {
        parent::eventBeforeProcessing();

        $skus = array();

        foreach ($this->listingsProducts as $listingProduct) {
            $skus[] = $this->getRequestDataObject($listingProduct)->getSku();
        }

        $this->addSkusToQueue($skus);
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

    protected function validateAndFilterListingsProducts()
    {
        $this->processGeneralValidateAndFilter();
        $this->processSkuGeneralValidateAndFilter();
        $this->processSkuSearchValidateAndFilter();
        $this->processSkuExistenceValidateAndFilter();
        $this->processListTypeValidateAndFilter();
    }

    //########################################

    private function processGeneralValidateAndFilter()
    {
        foreach ($this->listingsProducts as $listingProduct) {

            $validator = $this->getGeneralValidatorObject($listingProduct);

            $validationResult = $validator->validate();

            foreach ($validator->getMessages() as $message) {
                $this->getLogger()->logListingProductMessage(
                    $listingProduct,
                    $message['text'],
                    $message['type'],
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );
            }

            if ($validationResult) {
                $this->addValidatorsData($listingProduct, $validator->getData());
                continue;
            }

            $this->removeAndUnlockListingProduct($listingProduct->getId());
        }
    }

    private function processSkuGeneralValidateAndFilter()
    {
        foreach ($this->listingsProducts as $listingProduct) {

            $validator = $this->getSkuGeneralValidatorObject($listingProduct);

            $validationResult = $validator->validate();

            foreach ($validator->getMessages() as $message) {
                $this->getLogger()->logListingProductMessage(
                    $listingProduct,
                    $message['text'],
                    $message['type'],
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );
            }

            if ($validationResult) {
                $this->addValidatorsData($listingProduct, $validator->getData());
                continue;
            }

            $this->removeAndUnlockListingProduct($listingProduct->getId());
        }
    }

    private function processSkuSearchValidateAndFilter()
    {
        $requestSkus = array();
        $queueOfSkus = $this->getQueueOfSkus();

        foreach ($this->listingsProducts as $listingProduct) {

            $validator = $this->getSkuSearchValidatorObject($listingProduct);
            $validator->setRequestSkus($requestSkus);
            $validator->setQueueOfSkus($queueOfSkus);

            $validationResult = $validator->validate();

            foreach ($validator->getMessages() as $message) {
                $this->getLogger()->logListingProductMessage(
                    $listingProduct,
                    $message['text'],
                    $message['type'],
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );
            }

            if ($validationResult) {
                $requestSkus[] = $validator->getData('sku');
                $this->addValidatorsData($listingProduct, $validator->getData());
                continue;
            }

            $this->removeAndUnlockListingProduct($listingProduct->getId());
        }
    }

    private function processSkuExistenceValidateAndFilter()
    {
        /** @var Ess_M2ePro_Model_Listing_Product[][] $listingProductsPacks */
        $listingProductsPacks = array_chunk($this->listingsProducts,20,true);

        foreach ($listingProductsPacks as $listingProductsPack) {

            $skus = array();

            foreach ($listingProductsPack as $listingProduct) {
                $skus[] = $this->getValidatorsData($listingProduct, 'sku');
            }

            try {

                $countTriedTemp = 0;

                do {

                    $countTriedTemp != 0 && sleep(3);

                    /** @var $dispatcherObject Ess_M2ePro_Model_Connector_Amazon_Dispatcher */
                    $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Dispatcher');
                    $connectorObj = $dispatcherObject->getVirtualConnector('product','search','asinBySkus',
                                                                           array('include_info' => true,
                                                                                 'only_realtime' => true,
                                                                                 'items' => $skus),
                                                                           'items',
                                                                           $this->account->getId());
                    $response = $dispatcherObject->process($connectorObj);

                    if (is_null($response) && $connectorObj->hasErrorMessages()) {
                        throw new Ess_M2ePro_Model_Exception($connectorObj->getCombinedErrorMessage());
                    }

                } while (is_null($response) && ++$countTriedTemp <= 3);

                if (is_null($response)) {
                    throw new Ess_M2ePro_Model_Exception('Searching of SKU in your inventory on Amazon is not
                        available now. Please repeat the action later.');
                }

            } catch (Exception $exception) {

                Mage::helper('M2ePro/Module_Exception')->process($exception);

                foreach ($listingProductsPack as $listingProduct) {

                    $this->getLogger()->logListingProductMessage(
                        $listingProduct, Mage::helper('M2ePro')->__($exception->getMessage()),
                        Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                        Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                    );

                    $this->removeAndUnlockListingProduct($listingProduct->getId());
                }

                continue;
            }

            foreach ($listingProductsPack as $listingProduct) {
                $sku = $this->getValidatorsData($listingProduct, 'sku');
                $existenceResult = !empty($response[$sku]) ? $response[$sku] : array();

                $validator = $this->getSkuExistenceValidatorObject($listingProduct);
                $validator->setExistenceResult($existenceResult);

                $validationResult = $validator->validate();

                foreach ($validator->getMessages() as $message) {
                    $this->getLogger()->logListingProductMessage(
                        $listingProduct,
                        $message['text'],
                        $message['type'],
                        Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                    );
                }

                if ($validationResult) {
                    $this->addValidatorsData($listingProduct, $validator->getData());
                    continue;
                }

                $this->removeAndUnlockListingProduct($listingProduct->getId());
            }
        }
    }

    private function processListTypeValidateAndFilter()
    {
        $childGeneralIdsForParent = array();

        foreach ($this->listingsProducts as $listingProduct) {

            $validator = $this->getListTypeValidatorObject($listingProduct);

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();
            $variationManager = $amazonListingProduct->getVariationManager();

            if ($variationManager->isRelationChildType()) {
                $variationParentId = $variationManager->getVariationParentId();

                if (!isset($childGeneralIdsForParent[$variationParentId])) {
                    $childGeneralIdsForParent[$variationParentId] = array();
                }

                $validator->setChildGeneralIdsForParent(
                    $childGeneralIdsForParent[$variationParentId]
                );
            }

            $validationResult = $validator->validate();

            foreach ($validator->getMessages() as $message) {
                $this->getLogger()->logListingProductMessage(
                    $listingProduct,
                    $message['text'],
                    $message['type'],
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );
            }

            if ($validationResult) {
                $this->addValidatorsData($listingProduct, $validator->getData());

                if ($variationManager->isRelationChildType()) {
                    $variationParentId = $variationManager->getVariationParentId();
                    $childGeneralIdsForParent[$variationParentId][] = $this->getValidatorsData(
                        $listingProduct, 'general_id'
                    );
                }

                continue;
            }

            $this->removeAndUnlockListingProduct($listingProduct->getId());
        }
    }

    //########################################

    private function getValidatorsData(Ess_M2ePro_Model_Listing_Product $listingProduct, $key = null)
    {
        $listingProductId = (int)$listingProduct->getId();

        if (!isset($this->validatorsData[$listingProductId])) {
            $this->validatorsData[$listingProductId] = array();
        }

        if (is_null($key)) {
            return $this->validatorsData[$listingProductId];
        }

        return isset($this->validatorsData[$listingProductId][$key])
            ? $this->validatorsData[$listingProductId][$key] : null;
    }

    private function addValidatorsData(Ess_M2ePro_Model_Listing_Product $listingProduct, array $data)
    {
        $listingProductId = (int)$listingProduct->getId();

        if (!isset($this->validatorsData[$listingProductId])) {
            $this->validatorsData[$listingProductId] = array();
        }

        $this->validatorsData[$listingProductId] = array_merge($this->validatorsData[$listingProductId], $data);
    }

    //########################################

    private function addSkusToQueue(array $skus)
    {
        if (empty($skus)) {
            return;
        }

        /** @var Ess_M2ePro_Model_LockItem $lockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItem->setNick('amazon_list_skus_queue_' . $this->account->getId());

        if ($lockItem->isExist()) {
            $existSkus = $lockItem->getContentData();
        } else {
            $existSkus = array();
            $lockItem->create();
        }

        $skus = array_map('strval', $skus);
        $skus = array_merge($existSkus, $skus);

        $lockItem->setContentData($skus);
    }

    private function getQueueOfSkus()
    {
        /** @var Ess_M2ePro_Model_LockItem $lockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItem->setNick('amazon_list_skus_queue_' . $this->account->getId());

        if (!$lockItem->isExist()) {
            return array();
        }

        return $lockItem->getContentData();
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_General
     */
    private function getGeneralValidatorObject(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!isset($this->generalValidatorsObjects[$listingProduct->getId()])) {

            /** @var $validator Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_General */
            $validator = Mage::getModel(
                'M2ePro/Amazon_Listing_Product_Action_Type_List_Validator_General'
            );

            $validator->setParams($this->params);
            $validator->setListingProduct($listingProduct);
            $validator->setData($this->getValidatorsData($listingProduct));
            $validator->setConfigurator($listingProduct->getActionConfigurator());

            $this->generalValidatorsObjects[$listingProduct->getId()] = $validator;
        }

        return $this->generalValidatorsObjects[$listingProduct->getId()];
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_Sku_General
     */
    private function getSkuGeneralValidatorObject(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!isset($this->skuGeneralValidatorsObjects[$listingProduct->getId()])) {

            /** @var $validator Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_Sku_General */
            $validator = Mage::getModel(
                'M2ePro/Amazon_Listing_Product_Action_Type_List_Validator_Sku_General'
            );

            $validator->setParams($this->params);
            $validator->setListingProduct($listingProduct);
            $validator->setData($this->getValidatorsData($listingProduct));
            $validator->setConfigurator($listingProduct->getActionConfigurator());

            $this->skuGeneralValidatorsObjects[$listingProduct->getId()] = $validator;
        }

        return $this->skuGeneralValidatorsObjects[$listingProduct->getId()];
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_Sku_Search
     */
    private function getSkuSearchValidatorObject(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!isset($this->skuSearchValidatorsObjects[$listingProduct->getId()])) {

            /** @var $validator Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_Sku_Search */
            $validator = Mage::getModel(
                'M2ePro/Amazon_Listing_Product_Action_Type_List_Validator_Sku_Search'
            );

            $validator->setParams($this->params);
            $validator->setListingProduct($listingProduct);
            $validator->setData($this->getValidatorsData($listingProduct));
            $validator->setConfigurator($listingProduct->getActionConfigurator());

            $this->skuSearchValidatorsObjects[$listingProduct->getId()] = $validator;
        }

        return $this->skuSearchValidatorsObjects[$listingProduct->getId()];
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_Sku_Existence
     */
    private function getSkuExistenceValidatorObject(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!isset($this->skuExistenceValidatorsObjects[$listingProduct->getId()])) {

            /** @var $validator Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_Sku_Existence */
            $validator = Mage::getModel(
                'M2ePro/Amazon_Listing_Product_Action_Type_List_Validator_Sku_Existence'
            );

            $validator->setParams($this->params);
            $validator->setListingProduct($listingProduct);
            $validator->setData($this->getValidatorsData($listingProduct));
            $validator->setConfigurator($listingProduct->getActionConfigurator());

            $this->skuExistenceValidatorsObjects[$listingProduct->getId()] = $validator;
        }

        return $this->skuExistenceValidatorsObjects[$listingProduct->getId()];
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_ListType
     */
    private function getListTypeValidatorObject(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!isset($this->listTypeValidatorsObjects[$listingProduct->getId()])) {

            /** @var $validator Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_ListType */
            $validator = Mage::getModel(
                'M2ePro/Amazon_Listing_Product_Action_Type_List_Validator_ListType'
            );

            $validator->setParams($this->params);
            $validator->setListingProduct($listingProduct);
            $validator->setData($this->getValidatorsData($listingProduct));
            $validator->setConfigurator($listingProduct->getActionConfigurator());

            $this->listTypeValidatorsObjects[$listingProduct->getId()] = $validator;
        }

        return $this->listTypeValidatorsObjects[$listingProduct->getId()];
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Request
     */
    protected function getRequestObject(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!isset($this->requestsObjects[$listingProduct->getId()])) {

            /* @var $request Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Request */
            $request = Mage::getModel(
                'M2ePro/Amazon_Listing_Product_Action_Type_List_Request'
            );

            $request->setParams($this->params);
            $request->setListingProduct($listingProduct);
            $request->setConfigurator($listingProduct->getActionConfigurator());
            $request->setValidatorsData($this->getValidatorsData($listingProduct));

            $this->requestsObjects[$listingProduct->getId()] = $request;
        }

        return $this->requestsObjects[$listingProduct->getId()];
    }

    //########################################
}