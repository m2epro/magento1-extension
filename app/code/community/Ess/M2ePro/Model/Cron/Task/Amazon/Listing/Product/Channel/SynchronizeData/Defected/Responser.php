<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_Channel_SynchronizeData_Defected_Responser
    extends Ess_M2ePro_Model_Amazon_Connector_Inventory_Get_Defected_ItemsResponser
{
    protected $_logsActionId       = null;
    protected $_synchronizationLog = null;

    //########################################

    protected function processResponseMessages()
    {
        parent::processResponseMessages();

        foreach ($this->getResponse()->getMessages()->getEntities() as $message) {
            if (!$message->isError() && !$message->isWarning()) {
                continue;
            }

            $logType = $message->isError() ? Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
                : Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;

            $this->getSynchronizationLog()->addMessage(
                Mage::helper('M2ePro')->__($message->getText()),
                $logType,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );
        }
    }

    protected function isNeedProcessResponse()
    {
        if (!parent::isNeedProcessResponse()) {
            return false;
        }

        if ($this->getResponse()->getMessages()->hasErrorEntities()) {
            return false;
        }

        return true;
    }

    //########################################

    public function failDetected($messageText)
    {
        parent::failDetected($messageText);

        $this->getSynchronizationLog()->addMessage(
            Mage::helper('M2ePro')->__($messageText),
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
        );
    }

    //########################################

    protected function processResponseData()
    {
        try {
            $this->clearAllDefectedMessages();
            $this->updateReceivedDefectedListingsProducts();
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $this->getSynchronizationLog()->addMessage(
                Mage::helper('M2ePro')->__($exception->getMessage()),
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );
        }
    }

    //########################################

    protected function clearAllDefectedMessages()
    {
        if (!isset($this->_params['is_first_part']) || !$this->_params['is_first_part']) {
            return false;
        }

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingsProductsIds = $listingProductCollection->getAllIds();

        foreach (array_chunk($listingsProductsIds, 1000) as $partIds) {
            $where = '`listing_product_id` IN ('.implode(',', $partIds).')';
            $connWrite->update(
                Mage::getResourceModel('M2ePro/Amazon_Listing_Product')->getMainTable(), array(
                'defected_messages' => null,
                ), $where
            );
        }
    }

    protected function updateReceivedDefectedListingsProducts()
    {
        $responseData = $this->getPreparedResponseData();
        $receivedItems = $responseData['data'];

        $keys = array_map(
            function($el){
            return (string)$el; 
            }, array_keys($receivedItems)
        );

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('sku', array('in' => $keys));

        /** @var Ess_M2ePro_Model_Listing_Product[] $defectedListingsProducts */
        $defectedListingsProducts = $listingProductCollection->getItems();

        foreach ($defectedListingsProducts as $listingProduct) {

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();

            $receivedData = $receivedItems[$amazonListingProduct->getSku()];

            $defectedMessage = array(
                'attribute' => $receivedData['defected_attribute'],
                'value'     => $receivedData['current_value'],
                'type'      => $receivedData['defect_type'],
                'message'   => $receivedData['message'],
            );

            $listingProduct->setSettings('defected_messages', array($defectedMessage))->save();
        }
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getObjectByParam('Account', 'account_id');
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return $this->getAccount()->getChildObject()->getMarketplace();
    }

    //-----------------------------------------

    protected function getSynchronizationLog()
    {
        if ($this->_synchronizationLog !== null) {
            return $this->_synchronizationLog;
        }

        $this->_synchronizationLog = Mage::getModel('M2ePro/Synchronization_Log');
        $this->_synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);
        $this->_synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_LISTINGS);

        return $this->_synchronizationLog;
    }

    //########################################
}
