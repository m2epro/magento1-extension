<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Connector_Item_Dispatcher
{
    protected $_logsActionId = null;

    //########################################

    /**
     * @param int $action
     * @param array|Ess_M2ePro_Model_Listing_Product $products
     * @param array $params
     * @return int
     */
    public function process($action, $products, array $params = array())
    {
        $params = array_merge(
            array(
            'status_changer' => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN
            ), $params
        );

        if (empty($params['logs_action_id'])) {
            $this->_logsActionId      = Mage::getModel('M2ePro/Listing_Log')->getResource()->getNextActionId();
            $params['logs_action_id'] = $this->_logsActionId;
        } else {
            $this->_logsActionId = $params['logs_action_id'];
        }

        $isRealTime = !empty($params['is_realtime']);

        $products = $this->prepareProducts($products);
        $sortedProducts = $this->sortProductsByAccountsMarketplaces($products);

        return $this->processAccountsMarketplaces($sortedProducts, $action, $isRealTime, $params);
    }

    public function getLogsActionId()
    {
        return (int)$this->_logsActionId;
    }

    //########################################

    /**
     * @param array $sortedProducts
     * @param string $action
     * @param bool $isRealTime
     * @param array $params
     * @return int
     * @throws LogicException
     */
    protected function processAccountsMarketplaces(
        array $sortedProducts,
        $action,
        $isRealTime = false,
        array $params = array()
    ) {
        $results = array();

        foreach ($sortedProducts as $accountId => $accountProducts) {
            foreach ($accountProducts as $marketplaceId => $products) {
                if (empty($products)) {
                    continue;
                }

                try {
                    $result = $this->processProducts($products, $action, $isRealTime, $params);
                } catch (Exception $exception) {
                    foreach ($products as $product) {
                        /** @var Ess_M2ePro_Model_Listing_Product $product */

                        $this->logListingProductException($product, $exception, $action, $params);
                    }

                    Mage::helper('M2ePro/Module_Exception')->process($exception);
                    $result = Ess_M2ePro_Helper_Data::STATUS_ERROR;
                }

                $results[] = $result;
            }
        }

        return Mage::helper('M2ePro')->getMainStatus($results);
    }

    //########################################

    protected function processProducts(array $products, $action, $isRealTime = false, array $params = array())
    {
        /** @var Ess_M2ePro_Model_Ebay_Connector_Dispatcher $dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
        $connectorName = 'Ebay_Connector_Item_'.$this->getActionNick($action).'_Requester';

        $results = array();

        foreach ($products as $product) {
            /** @var Ess_M2ePro_Model_Listing_Product $product */

            try {

                /** @var Ess_M2ePro_Model_Ebay_Connector_Item_Requester $connector */
                $connector = $dispatcher->getCustomConnector($connectorName, $params);
                $connector->setIsRealTime($isRealTime);
                $connector->setListingProduct($product);

                $dispatcher->process($connector);
                $result = $connector->getStatus();

                $logsActionId = $connector->getLogsActionId();
                // When additional action runs using processing, there is no status for it
                if (is_array($logsActionId) && $isRealTime) {
                    $this->_logsActionId = max($logsActionId);
                    $result              = Mage::getModel('M2ePro/Listing_Log')
                                               ->getResource()
                                               ->getStatusByActionId($logsActionId);
                } else {
                    $this->_logsActionId = $logsActionId;
                }
            } catch (Exception $exception) {
                $this->logListingProductException($product, $exception, $action, $params);
                Mage::helper('M2ePro/Module_Exception')->process($exception);

                $result = Ess_M2ePro_Helper_Data::STATUS_ERROR;
            }

            $results[] = $result;
        }

        return Mage::helper('M2ePro')->getMainStatus($results);
    }

    //########################################

    protected function prepareProducts($products)
    {
        $productsTemp = array();

        if (!is_array($products)) {
            $products = array($products);
        }

        $productsIdsTemp = array();
        foreach ($products as $product) {
            $tempProduct = null;
            if ($product instanceof Ess_M2ePro_Model_Listing_Product) {
                $tempProduct = $product;
            } else {
                $tempProduct = Mage::helper('M2ePro/Component_Ebay')->getObject('Listing_Product', (int)$product);
            }

            if (in_array((int)$tempProduct->getId(), $productsIdsTemp)) {
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

    protected function logListingProductException(
        Ess_M2ePro_Model_Listing_Product $listingProduct,
        Exception $exception,
        $action,
        $params
    ) {
        $logModel = Mage::getModel('M2ePro/Listing_Log');
        $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

        $action = $this->recognizeActionForLogging($action, $params);
        $initiator = $this->recognizeInitiatorForLogging($params);

        $logModel->addProductMessage(
            $listingProduct->getListingId(),
            $listingProduct->getProductId(),
            $listingProduct->getId(),
            $initiator,
            $this->_logsActionId,
            $action,
            $exception->getMessage(),
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
        );
    }

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

    //########################################

    protected function getActionNick($action)
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

            default:
                throw new Ess_M2ePro_Model_Exception_Logic('Unknown action');
        }
    }

    //########################################
}
