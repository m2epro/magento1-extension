<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Ebay_OtherItem_Dispatcher
{
    private $logsActionId = NULL;

    //########################################

    /**
     * @param int $action
     * @param array|Ess_M2ePro_Model_Listing_Other $products
     * @param array $params
     * @return int
     */
    public function process($action, $products, array $params = array())
    {
        $params = array_merge(array(
            'status_changer' => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN
        ), $params);

        $this->logsActionId = Mage::getModel('M2ePro/Listing_Other_Log')->getNextActionId();
        $params['logs_action_id'] = $this->logsActionId;

        $products = $this->prepareProducts($products);

        switch ($action) {

            case Ess_M2ePro_Model_Listing_Product::ACTION_RELIST:
                $result = $this->processProducts(
                    $products, 'Ess_M2ePro_Model_Connector_Ebay_OtherItem_Relist_Single', $params
                );
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_STOP:
                $result = $this->processProducts(
                    $products, 'Ess_M2ePro_Model_Connector_Ebay_OtherItem_Stop_Single', $params
                );
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_REVISE:
                $result = $this->processProducts(
                    $products, 'Ess_M2ePro_Model_Connector_Ebay_OtherItem_Revise_Single', $params
                );
                break;

            default;
                $result = Ess_M2ePro_Helper_Data::STATUS_ERROR;
                break;
        }

        return $result;
    }

    /**
     * @return int
     */
    public function getLogsActionId()
    {
        return (int)$this->logsActionId;
    }

    //########################################

    /**
     * @param array $products
     * @param string $connectorNameSingle
     * @param array $params
     * @return int
     * @throws LogicException
     */
    protected function processProducts(array $products, $connectorNameSingle, array $params = array())
    {
        $results = array();

        if (empty($products)) {
            return Mage::helper('M2ePro')->getMainStatus($results);
        }

        if (!class_exists($connectorNameSingle)) {
            return Mage::helper('M2ePro')->getMainStatus($results);
        }

        try {

            foreach ($products as $product) {
                $connector = new $connectorNameSingle($params,$product);
                $connector->process();
                $results[] = $connector->getStatus();
            }

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $logModel = Mage::getModel('M2ePro/Listing_Other_Log');
            $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

            $action = $this->recognizeActionForLogging($connectorNameSingle);
            $initiator = $this->recognizeInitiatorForLogging($params);

            foreach ($products as $product) {

                /** @var Ess_M2ePro_Model_Listing_Other $product */

                $logModel->addProductMessage(
                    $product->getId(),
                    $initiator,
                    $this->logsActionId,
                    $action,
                    Mage::helper('M2ePro')->__($exception->getMessage()),
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
                );
            }

            $results[] = Ess_M2ePro_Helper_Data::STATUS_ERROR;
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

            $tempProduct = NULL;
            if ($product instanceof Ess_M2ePro_Model_Listing_Other) {
                $tempProduct = $product;
            } else {
                $tempProduct = Mage::helper('M2ePro/Component_Ebay')->getObject('Listing_Other',(int)$product);
            }

            if (in_array((int)$tempProduct->getId(),$productsIdsTemp)) {
                continue;
            }

            $productsIdsTemp[] = (int)$tempProduct->getId();
            $productsTemp[] = $tempProduct;
        }

        return $productsTemp;
    }

    // ---------------------------------------

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

    protected function recognizeActionForLogging($connectorNameSingle)
    {
        $action = Ess_M2ePro_Model_Listing_Log::ACTION_UNKNOWN;

        switch ($connectorNameSingle)
        {
            case 'Ess_M2ePro_Model_Connector_Ebay_OtherItem_Relist_Single':
                $action = Ess_M2ePro_Model_Listing_Other_Log::ACTION_RELIST_PRODUCT;
                break;
            case 'Ess_M2ePro_Model_Connector_Ebay_OtherItem_Revise_Single':
                $action = Ess_M2ePro_Model_Listing_Other_Log::ACTION_REVISE_PRODUCT;
                break;
            case 'Ess_M2ePro_Model_Connector_Ebay_OtherItem_Stop_Single':
                $action = Ess_M2ePro_Model_Listing_Other_Log::ACTION_STOP_PRODUCT;
                break;
        }

        return $action;
    }

    //########################################
}