<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Buy_Product_Dispatcher
{
    private $logsActionId = NULL;
    private $isProcessingItems = false;

    //########################################

    /**
     * @param int $action
     * @param array|Ess_M2ePro_Model_Listing_Product $products
     * @param array $params
     * @return int
     */
    public function process($action, $products, array $params = array())
    {
        $params = array_merge(array(
            'status_changer' => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN
        ), $params);

        if (empty($params['logs_action_id'])) {
            $this->logsActionId = Mage::getModel('M2ePro/Listing_Log')->getNextActionId();
            $params['logs_action_id'] = $this->logsActionId;
        } else {
            $this->logsActionId = $params['logs_action_id'];
        }

        $products = $this->prepareProducts($products);

        if ($action == Ess_M2ePro_Model_Listing_Product::ACTION_LIST) {

            $newSkuProducts = $listProducts = array();

            foreach ($products as $product) {

                /** @var $product Ess_M2ePro_Model_Listing_Product */

                if ($product->getChildObject()->getTemplateNewProductId() &&
                    !$product->getChildObject()->getGeneralId()) {

                    $tempId = $product->getChildObject()->getTemplateNewProductId();
                    !isset($newSkuProducts[$tempId]) && $newSkuProducts[$tempId] = array();
                    $newSkuProducts[$tempId][] = $product;

                    continue;
                }

                $listProducts[] = $product;
            }

            $results = array();

            foreach ($newSkuProducts as $newSkuProductsTemp) {
                $sortedProductsNewSkuData = $this->sortProductsByAccount($newSkuProductsTemp);
                $results[] = $this->processGroupedProducts(
                    $sortedProductsNewSkuData,
                    10,
                    'Ess_M2ePro_Model_Connector_Buy_Product_NewSku_MultipleRequester',
                    $params
                );
            }

            if (count($listProducts) > 0) {
                $sortedProductsListData = $this->sortProductsByAccount($listProducts);
                $results[] = $this->processGroupedProducts(
                    $sortedProductsListData,
                    100,
                    'Ess_M2ePro_Model_Connector_Buy_Product_List_MultipleRequester',
                    $params
                );
            }

            if (count($results) <= 0) {
                $results[] = Ess_M2ePro_Helper_Data::STATUS_ERROR;
            }

            return Mage::helper('M2ePro')->getMainStatus($results);
        }

        $sortedProductsData = $this->sortProductsByAccount($products);

        switch ($action) {
            case Ess_M2ePro_Model_Listing_Product::ACTION_RELIST:
                $result = $this->processGroupedProducts(
                    $sortedProductsData,
                    1000,
                    'Ess_M2ePro_Model_Connector_Buy_Product_Relist_MultipleRequester',
                    $params
                );
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_REVISE:
                $result = $this->processGroupedProducts(
                    $sortedProductsData,
                    1000,
                    'Ess_M2ePro_Model_Connector_Buy_Product_Revise_MultipleRequester',
                    $params
                );
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_STOP:
                $result = $this->processGroupedProducts(
                    $sortedProductsData,
                    1000,
                    'Ess_M2ePro_Model_Connector_Buy_Product_Stop_MultipleRequester',
                    $params
                );
                break;

            default;
                $result = Ess_M2ePro_Helper_Data::STATUS_ERROR;
                break;
        }

        return $result;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getLogsActionId()
    {
        return (int)$this->logsActionId;
    }

    /**
     * @return bool
     */
    public function isProcessingItems()
    {
        return (bool)$this->isProcessingItems;
    }

    //########################################

    /**
     * @param array $sortedProductsData
     * @param int $maxProductsForOneRequest
     * @param string $connectorName
     * @param array $params
     * @throws LogicException
     * @return int
     */
    protected function processGroupedProducts(array $sortedProductsData,
                                              $maxProductsForOneRequest,
                                              $connectorName,
                                              array $params = array())
    {
        $results = array();

        foreach ($sortedProductsData as $products) {

            if (count($products) <= 0 || !class_exists($connectorName)) {
                continue;
            }

            if (is_null($maxProductsForOneRequest)) {
                $results[] = $this->processProducts($products, $connectorName, $params);
            } else {
                for ($i=0; $i<count($products);$i+=$maxProductsForOneRequest) {
                    $productsForRequest = array_slice($products,$i,$maxProductsForOneRequest);
                    $results[] = $this->processProducts($productsForRequest, $connectorName, $params);
                }
            }
        }

        return Mage::helper('M2ePro')->getMainStatus($results);
    }

    /**
     * @param array $products
     * @param string $connectorName
     * @param array $params
     * @return int
     */
    protected function processProducts(array $products, $connectorName, array $params = array())
    {
        try {

            $connector = new $connectorName($params,$products);
            $connector->process();

            $this->isProcessingItems = $connector->isProcessingItems();

            return $connector->getStatus();

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $logModel = Mage::getModel('M2ePro/Buy_Listing_Log');

            $action = $this->recognizeActionForLogging($connectorName,$params);
            $initiator = $this->recognizeInitiatorForLogging($params);

            foreach ($products as $product) {

                /** @var Ess_M2ePro_Model_Listing_Product $product */

                if ($product->isDeleted()) {
                    continue;
                }

                $logModel->addProductMessage(
                    $product->getListingId(),
                    $product->getProductId(),
                    $product->getId(),
                    $initiator,
                    $this->logsActionId,
                    $action,
                    $exception->getMessage(),
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
                );
            }

            return Ess_M2ePro_Helper_Data::STATUS_ERROR;
        }
    }

    //########################################

    /**
     * @param array|Ess_M2ePro_Model_Listing_Product $products
     * @return array
     */
    protected function prepareProducts($products)
    {
        $productsTemp = array();

        if (!is_array($products)) {
            $products = array($products);
        }

        $productsIdsTemp = array();
        foreach ($products as $product) {

            $tempProduct = NULL;
            if ($product instanceof Ess_M2ePro_Model_Listing_Product) {
                $tempProduct = $product;
            } else {
                $tempProduct = Mage::helper('M2ePro/Component_Buy')->getObject('Listing_Product',(int)$product);
            }

            if (in_array((int)$tempProduct->getId(),$productsIdsTemp)) {
                continue;
            }

            $productsIdsTemp[] = (int)$tempProduct->getId();
            $productsTemp[] = $tempProduct;
        }

        return $productsTemp;
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Product[] $products
     * @return array
     */
    protected function sortProductsByAccount($products)
    {
        $sortedProducts = array();

        /** @var $product Ess_M2ePro_Model_Listing_Product */
        foreach ($products as $product) {
            $accountId = $product->getListing()->getAccountId();
            $sortedProducts[$accountId][] = $product;
        }

        return array_values($sortedProducts);
    }

    // ---------------------------------------

    /**
     * @param array $params
     * @return int
     */
    protected function recognizeInitiatorForLogging(array $params)
    {
        $statusChanger = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN;
        isset($params['status_changer']) && $statusChanger = $params['status_changer'];

        $initiator = Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION;

        if ($statusChanger == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN) {
            $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;
        } else if ($statusChanger == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
            $initiator = Ess_M2ePro_Helper_Data::INITIATOR_USER;
        }

        return $initiator;
    }

    /**
     * @param string $connectorName
     * @param array $params
     * @return int
     */
    protected function recognizeActionForLogging($connectorName, array $params)
    {
        $action = Ess_M2ePro_Model_Listing_Log::ACTION_UNKNOWN;

        switch ($connectorName)
        {
            case 'Ess_M2ePro_Model_Connector_Buy_Product_NewSku_MultipleRequester':
                $action = Ess_M2ePro_Model_Listing_Log::ACTION_NEW_SKU_PRODUCT_ON_COMPONENT;
                break;
            case 'Ess_M2ePro_Model_Connector_Buy_Product_List_MultipleRequester':
                $action = Ess_M2ePro_Model_Listing_Log::ACTION_LIST_PRODUCT_ON_COMPONENT;
                break;
            case 'Ess_M2ePro_Model_Connector_Buy_Product_Relist_MultipleRequester':
                $action = Ess_M2ePro_Model_Listing_Log::ACTION_RELIST_PRODUCT_ON_COMPONENT;
                break;
            case 'Ess_M2ePro_Model_Connector_Buy_Product_Revise_MultipleRequester':
                $action = Ess_M2ePro_Model_Listing_Log::ACTION_REVISE_PRODUCT_ON_COMPONENT;
                break;
            case 'Ess_M2ePro_Model_Connector_Buy_Product_Stop_MultipleRequester':
                if (isset($params['remove']) && (bool)$params['remove']) {
                    $action = Ess_M2ePro_Model_Listing_Log::ACTION_STOP_AND_REMOVE_PRODUCT;
                } else {
                    $action = Ess_M2ePro_Model_Listing_Log::ACTION_STOP_PRODUCT_ON_COMPONENT;
                }
                break;
        }

        return $action;
    }

    //########################################
}