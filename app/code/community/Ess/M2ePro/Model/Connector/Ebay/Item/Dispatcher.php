<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher
{
    private $logsActionId = NULL;

    // ########################################

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

        $this->logsActionId = Mage::getModel('M2ePro/Listing_Log')->getNextActionId();
        $params['logs_action_id'] = $this->logsActionId;

        $products = $this->prepareProducts($products);
        $sortedProducts = $this->sortProductsByAccountsMarketplaces($products);

        switch ($action) {

            case Ess_M2ePro_Model_Listing_Product::ACTION_LIST:
                $result = $this->processAccountsMarketplaces(
                    $sortedProducts, 5, 'Ess_M2ePro_Model_Connector_Ebay_Item_List_Single',
                    'Ess_M2ePro_Model_Connector_Ebay_Item_List_Multiple', $params
                );
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_RELIST:
                $result = $this->processAccountsMarketplaces(
                    $sortedProducts, NULL, 'Ess_M2ePro_Model_Connector_Ebay_Item_Relist_Single',
                    NULL, $params
                );
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_REVISE:
                $result = $this->processAccountsMarketplaces(
                    $sortedProducts, NULL, 'Ess_M2ePro_Model_Connector_Ebay_Item_Revise_Single',
                    NULL, $params
                );
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_STOP:
                $result = $this->processAccountsMarketplaces(
                    $sortedProducts, 10, 'Ess_M2ePro_Model_Connector_Ebay_Item_Stop_Single',
                    'Ess_M2ePro_Model_Connector_Ebay_Item_Stop_Multiple', $params
                );
                break;

            default;
                $result = Ess_M2ePro_Helper_Data::STATUS_ERROR;
                break;
        }

        return $result;
    }

    public function getLogsActionId()
    {
        return (int)$this->logsActionId;
    }

    // ########################################

    /**
     * @param array $sortedProducts
     * @param int $maxProductsForOneRequest
     * @param string $connectorNameSingle
     * @param string|null $connectorNameMultiple
     * @param array $params
     * @return int
     * @throws LogicException
     */
    protected function processAccountsMarketplaces(array $sortedProducts,
                                                   $maxProductsForOneRequest,
                                                   $connectorNameSingle,
                                                   $connectorNameMultiple = NULL,
                                                   array $params = array())
    {
        $results = array();

        if (!class_exists($connectorNameSingle)) {
            return Mage::helper('M2ePro')->getMainStatus($results);
        }

        if (!is_null($connectorNameMultiple) && !class_exists($connectorNameMultiple)) {
            return Mage::helper('M2ePro')->getMainStatus($results);
        }

        foreach ($sortedProducts as $accountId => $accountProducts) {
            foreach ($accountProducts as $marketplaceId => $products) {

                if (empty($products)) {
                    continue;
                }

                if (is_null($maxProductsForOneRequest)) {

                    $results[] = $this->processProducts(
                        $accountId, $marketplaceId,
                        $products,
                        $connectorNameSingle, $connectorNameMultiple,
                        $params
                    );

                    continue;
                }

                for ($i=0; $i<count($products);$i+=$maxProductsForOneRequest) {

                    $productsForRequest = array_slice($products,$i,$maxProductsForOneRequest);

                    $results[] = $this->processProducts(
                        $accountId, $marketplaceId,
                        $productsForRequest,
                        $connectorNameSingle, $connectorNameMultiple,
                        $params
                    );
                }
            }
        }

        return Mage::helper('M2ePro')->getMainStatus($results);
    }

    /**
     * @param int $accountId
     * @param int $marketplaceId
     * @param array $products
     * @param string $connectorNameSingle
     * @param string|null $connectorNameMultiple
     * @param array $params
     * @return int
     */
    protected function processProducts($accountId, $marketplaceId, array $products,
                                       $connectorNameSingle,
                                       $connectorNameMultiple = NULL,
                                       array $params = array())
    {
        try {

            if (count($products) > 1) {

                if (is_null($connectorNameMultiple)) {

                    $results = array();

                    foreach ($products as $product) {

                        $results[] = $this->processProducts(
                            $accountId, $marketplaceId,
                            array($product),
                            $connectorNameSingle, $connectorNameMultiple,
                            $params
                        );
                    }

                    return Mage::helper('M2ePro')->getMainStatus($results);
                }

                $productsInstances = array();
                foreach ($products as $product) {
                    $productsInstances[] = $product;
                }

                $connector = new $connectorNameMultiple($params,$productsInstances);

            } else {
                $productInstance = $products[0];
                $connector = new $connectorNameSingle($params,$productInstance);
            }

            $connector->process();

            return $connector->getStatus();

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $logModel = Mage::getModel('M2ePro/Listing_Log');
            $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

            $action = $this->recognizeActionForLogging($connectorNameSingle,$connectorNameMultiple,$params);
            $initiator = $this->recognizeInitiatorForLogging($params);

            foreach ($products as $product) {

                /** @var Ess_M2ePro_Model_Listing_Product $product */

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

    // ########################################

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
                $tempProduct = Mage::helper('M2ePro/Component_Ebay')->getObject('Listing_Product',(int)$product);
            }

            if (in_array((int)$tempProduct->getId(),$productsIdsTemp)) {
                continue;
            }

            $productsIdsTemp[] = (int)$tempProduct->getId();
            $productsTemp[] = $tempProduct;
        }

        return $productsTemp;
    }

    protected function sortProductsByAccountsMarketplaces($products)
    {
        $sortedProducts = array();

        foreach ($products as $product) {

            /** @var Ess_M2ePro_Model_Listing_Product $product */

            $accountId     = $product->getListing()->getAccountId();
            $marketplaceId = $product->getListing()->getMarketplaceId();

            $sortedProducts[$accountId][$marketplaceId][] = $product;
        }

        return $sortedProducts;
    }

    // ----------------------------------------

    protected function recognizeInitiatorForLogging(array $params)
    {
        $statusChanger = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN;
        isset($params['status_changer']) && $statusChanger = $params['status_changer'];

        $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;

        if ($statusChanger == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN) {
            $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;
        } else if ($statusChanger == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
            $initiator = Ess_M2ePro_Helper_Data::INITIATOR_USER;
        } else {
            $initiator = Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION;
        }

        return $initiator;
    }

    protected function recognizeActionForLogging($connectorNameSingle, $connectorNameMultiple, array $params)
    {
        $action = Ess_M2ePro_Model_Listing_Log::ACTION_UNKNOWN;

        switch ($connectorNameSingle)
        {
            case 'Ess_M2ePro_Model_Connector_Ebay_Item_List_Single':
                $action = Ess_M2ePro_Model_Listing_Log::ACTION_LIST_PRODUCT_ON_COMPONENT;
                break;
            case 'Ess_M2ePro_Model_Connector_Ebay_Item_Relist_Single':
                $action = Ess_M2ePro_Model_Listing_Log::ACTION_RELIST_PRODUCT_ON_COMPONENT;
                break;
            case 'Ess_M2ePro_Model_Connector_Ebay_Item_Revise_Single':
                $action = Ess_M2ePro_Model_Listing_Log::ACTION_REVISE_PRODUCT_ON_COMPONENT;
                break;
            case 'Ess_M2ePro_Model_Connector_Ebay_Item_Stop_Single':
                if (isset($params['remove']) && (bool)$params['remove']) {
                    $action = Ess_M2ePro_Model_Listing_Log::ACTION_STOP_AND_REMOVE_PRODUCT;
                } else {
                    $action = Ess_M2ePro_Model_Listing_Log::ACTION_STOP_PRODUCT_ON_COMPONENT;
                }
                break;
        }

        switch ($connectorNameMultiple)
        {
            case 'Ess_M2ePro_Model_Connector_Ebay_Item_List_Multiple':
                $action = Ess_M2ePro_Model_Listing_Log::ACTION_LIST_PRODUCT_ON_COMPONENT;
                break;
            case 'Ess_M2ePro_Model_Connector_Ebay_Item_Stop_Multiple':
                if (isset($params['remove']) && (bool)$params['remove']) {
                    $action = Ess_M2ePro_Model_Listing_Log::ACTION_STOP_AND_REMOVE_PRODUCT;
                } else {
                    $action = Ess_M2ePro_Model_Listing_Log::ACTION_STOP_PRODUCT_ON_COMPONENT;
                }
                break;
        }

        return $action;
    }

    // ########################################
}