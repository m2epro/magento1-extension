<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Connector_Product_Dispatcher
{
    private $logsActionId = NULL;

    private $processingActionsIds = array();

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

        if (empty($params['logs_action_id'])) {
            $this->logsActionId = Mage::getModel('M2ePro/Listing_Log')->getResource()->getNextActionId();
            $params['logs_action_id'] = $this->logsActionId;
        } else {
            $this->logsActionId = $params['logs_action_id'];
        }

        $products = $this->prepareProducts($products);
        $sortedProducts = $this->sortProductsByAccount($products);

        return $this->processGroupedProducts($sortedProducts, $action, $params);
    }

    //-----------------------------------------

    public function getLogsActionId()
    {
        return (int)$this->logsActionId;
    }

    //-----------------------------------------

    public function getProcessingActionsIds()
    {

    }

    // ########################################

    /**
     * @param array $sortedProductsData
     * @param string $action
     * @param array $params
     * @throws LogicException
     * @return int
     */
    protected function processGroupedProducts(array $sortedProductsData,
                                              $action,
                                              array $params = array())
    {
        $results = array();

        foreach ($sortedProductsData as $products) {

            if (empty($products)) {
                continue;
            }

            foreach ($products as $product) {
                $results[] = $this->processProduct($product, $action, $params);
            }
        }

        return Mage::helper('M2ePro')->getMainStatus($results);
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Product $product
     * @param string $action
     * @param array $params
     * @return int
     */
    protected function processProduct(Ess_M2ePro_Model_Listing_Product $product, $action, array $params = array())
    {
        try {

            $dispatcher = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');
            $connectorName = 'Walmart_Connector_Product_'.$this->getActionNick($action).'_Requester';

            /** @var Ess_M2ePro_Model_Walmart_Connector_Product_Requester $connector */
            $connector = $dispatcher->getCustomConnector($connectorName, $params);
            $connector->setListingProduct($product);

            $dispatcher->process($connector);

            return $connector->getStatus();

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $logModel = Mage::getModel('M2ePro/Walmart_Listing_Log');

            $action = $this->recognizeActionForLogging($action, $params);
            $initiator = $this->recognizeInitiatorForLogging($params);

            /** @var Ess_M2ePro_Model_Listing_Product $product */

            if (!$product->isDeleted()) {
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
        if (!is_array($products)) {
            $products = array($products);
        }

        $preparedProducts     = array();
        $parentsForProcessing = array();

        foreach ($products as $listingProduct) {

            if (is_numeric($listingProduct)) {
                if (isset($preparedProducts[(int)$listingProduct])) {
                    continue;
                }

                $listingProduct = Mage::helper('M2ePro/Component_Walmart')->getObject(
                    'Listing_Product', (int)$listingProduct
                );
            }

            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */

            if (isset($preparedProducts[(int)$listingProduct->getId()])) {
                continue;
            }

            $preparedProducts[(int)$listingProduct->getId()] = $listingProduct;

            /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
            $walmartListingProduct = $listingProduct->getChildObject();
            $variationManager = $walmartListingProduct->getVariationManager();

            if (!$variationManager->isRelationMode()) {
                continue;
            }

            if ($variationManager->isRelationParentType()) {
                $parentListingProduct = $listingProduct;
            } else {
                $parentListingProduct = $variationManager->getTypeModel()->getParentListingProduct();
            }

            /** @var Ess_M2ePro_Model_Walmart_Listing_Product $parentWalmartListingProduct */
            $parentWalmartListingProduct = $parentListingProduct->getChildObject();

            if (!$parentWalmartListingProduct->getVariationManager()->getTypeModel()->isNeedProcessor()) {
                continue;
            }

            $parentsForProcessing[$parentListingProduct->getId()] = $parentListingProduct;
        }

        if (empty($parentsForProcessing)) {
            return $preparedProducts;
        }

        $massProcessor = Mage::getModel(
            'M2ePro/Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Mass'
        );
        $massProcessor->setListingsProducts($parentsForProcessing);

        $massProcessor->execute();

        $actionConfigurators = array();
        foreach ($preparedProducts as $id => $listingProduct) {
            if (is_null($listingProduct->getActionConfigurator())) {
                continue;
            }

            $actionConfigurators[$id] = $listingProduct->getActionConfigurator();
        }

        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('id', array('in' => array_keys($preparedProducts)));

        /** @var Ess_M2ePro_Model_Listing_Product[] $actualListingsProducts */
        $actualListingsProducts = $listingProductCollection->getItems();

        if (empty($actualListingsProducts)) {
            return array();
        }

        foreach ($actualListingsProducts as $id => $actualListingProduct) {
            if (is_null($actionConfigurators[$id])) {
                continue;
            }

            $actualListingProduct->setActionConfigurator($actionConfigurators[$id]);
        }

        return $actualListingsProducts;
    }

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

    // ----------------------------------------

    protected function recognizeInitiatorForLogging(array $params)
    {
        $statusChanger = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN;
        isset($params['status_changer']) && $statusChanger = $params['status_changer'];

        if ($statusChanger == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN) {
            $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;
        } else if ($statusChanger == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
            $initiator = Ess_M2ePro_Helper_Data::INITIATOR_USER;
        } else {
            $initiator = Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION;
        }

        return $initiator;
    }

    protected function recognizeActionForLogging($action, array $params)
    {
        $logAction = Ess_M2ePro_Model_Listing_Log::ACTION_UNKNOWN;

        switch ($action)
        {
            case Ess_M2ePro_Model_Listing_Product::ACTION_DELETE:
                if (isset($params['remove']) && (bool)$params['remove']) {
                    $logAction = Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_AND_REMOVE_PRODUCT;
                } else {
                    $logAction = Ess_M2ePro_Model_Listing_Log::_ACTION_DELETE_PRODUCT_FROM_COMPONENT;
                }
                break;
            case Ess_M2ePro_Model_Listing_Product::ACTION_LIST:
                $logAction = Ess_M2ePro_Model_Listing_Log::ACTION_LIST_PRODUCT_ON_COMPONENT;
                break;
            case Ess_M2ePro_Model_Listing_Product::ACTION_RELIST:
                $logAction = Ess_M2ePro_Model_Listing_Log::ACTION_RELIST_PRODUCT_ON_COMPONENT;
                break;
            case Ess_M2ePro_Model_Listing_Product::ACTION_REVISE:
                $logAction = Ess_M2ePro_Model_Listing_Log::ACTION_REVISE_PRODUCT_ON_COMPONENT;
                break;
            case Ess_M2ePro_Model_Listing_Product::ACTION_STOP:
                if (isset($params['remove']) && (bool)$params['remove']) {
                    $logAction = Ess_M2ePro_Model_Listing_Log::ACTION_STOP_AND_REMOVE_PRODUCT;
                } else {
                    $logAction = Ess_M2ePro_Model_Listing_Log::ACTION_STOP_PRODUCT_ON_COMPONENT;
                }
                break;
        }

        return $logAction;
    }

    // ########################################

    private function getActionNick($action)
    {
        switch ($action) {
            case Ess_M2ePro_Model_Listing_Product::ACTION_LIST:
                return 'List';

            case Ess_M2ePro_Model_Listing_Product::ACTION_RELIST:
                return 'Relist';

            case Ess_M2ePro_Model_Listing_Product::ACTION_REVISE:
                return 'Revise';

            case Ess_M2ePro_Model_Listing_Product::ACTION_STOP:
                return 'Stop';

            case Ess_M2ePro_Model_Listing_Product::ACTION_DELETE:
                return 'Delete';

            default:
                throw new Ess_M2ePro_Model_Exception_Logic('Unknown action');
        }
    }

    // ########################################
}