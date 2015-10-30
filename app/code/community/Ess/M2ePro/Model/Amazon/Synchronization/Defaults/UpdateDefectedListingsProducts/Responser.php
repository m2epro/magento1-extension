<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Synchronization_Defaults_UpdateDefectedListingsProducts_Responser
    extends Ess_M2ePro_Model_Connector_Amazon_Inventory_Get_Defected_ItemsResponser
{
    protected $logsActionId = NULL;
    protected $synchronizationLog = NULL;

    //########################################

    protected function processResponseMessages(array $messages = array())
    {
        parent::processResponseMessages($messages);

        foreach ($this->messages as $message) {

            if (!$this->isMessageError($message) && !$this->isMessageWarning($message)) {
                continue;
            }

            $logType = $this->isMessageError($message) ? Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
                                                       : Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;

            $this->getSynchronizationLog()->addMessage(
                Mage::helper('M2ePro')->__($message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY]),
                $logType,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );
        }
    }

    protected function isNeedToParseResponseData($responseBody)
    {
        if (!parent::isNeedToParseResponseData($responseBody)) {
            return false;
        }

        if ($this->hasErrorMessages()) {
            return false;
        }

        return true;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Processing_Request $processingRequest
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function unsetProcessingLocks(Ess_M2ePro_Model_Processing_Request $processingRequest)
    {
        parent::unsetProcessingLocks($processingRequest);

        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');

        $tempNick = Ess_M2ePro_Model_Amazon_Synchronization_Defaults_UpdateDefectedListingsProducts::LOCK_ITEM_PREFIX;
        $tempNick .= '_'.$this->params['account_id'];

        $lockItem->setNick($tempNick);
        $lockItem->setMaxInactiveTime(Ess_M2ePro_Model_Processing_Request::MAX_LIFE_TIME_INTERVAL);

        $lockItem->remove();

        $this->getAccount()->deleteObjectLocks(NULL, $processingRequest->getHash());
        $this->getAccount()->deleteObjectLocks('synchronization', $processingRequest->getHash());
        $this->getAccount()->deleteObjectLocks('synchronization_amazon', $processingRequest->getHash());
        $this->getAccount()->deleteObjectLocks(
            Ess_M2ePro_Model_Amazon_Synchronization_Defaults_UpdateDefectedListingsProducts::LOCK_ITEM_PREFIX,
            $processingRequest->getHash()
        );
    }

    public function eventFailedExecuting($message)
    {
        parent::eventFailedExecuting($message);

        $this->getSynchronizationLog()->addMessage(
            Mage::helper('M2ePro')->__($message),
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
        );
    }

    public function eventAfterExecuting()
    {
        parent::eventAfterExecuting();

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tempTable = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_processed_inventory');
        $connWrite->delete($tempTable, array('`hash` = ?' => (string)$this->params['processed_inventory_hash']));
    }

    //########################################

    protected function processResponseData($response)
    {
        try {

            $this->updateReceivedDefectedListingsProducts($response['data']);
            $this->updateNotReceivedDefectedListingsProducts($response['data'], $response['next_part']);

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

    protected function updateReceivedDefectedListingsProducts($receivedItems)
    {
        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('sku', array('in' => array_keys($receivedItems)));

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

    protected function updateNotReceivedDefectedListingsProducts($receivedItems,$nextPart)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tempTable = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_processed_inventory');

        // ---------------------------------------

        foreach (array_chunk($receivedItems,1000) as $partReceivedItems) {

            $inserts = array();
            foreach ($partReceivedItems as $partReceivedItem) {
                $inserts[] = array(
                    'sku'  => $partReceivedItem['sku'],
                    'hash' => $this->params['processed_inventory_hash']
                );
            }

            $connWrite->insertMultiple($tempTable, $inserts);
        }
        // ---------------------------------------

        if (!is_null($nextPart)) {
            return;
        }

        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();

        /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $collection->getSelect()->join(array('l' => $listingTable), 'main_table.listing_id = l.id', array());
        $collection->getSelect()->joinLeft(
            array('api' => $tempTable),
            '`second_table`.sku = `api`.sku AND `api`.`hash` = \''.$this->params['processed_inventory_hash'].'\'',
            array('sku')
        );
        $collection->getSelect()->where('l.account_id = ?',(int)$this->getAccount()->getId());
        $collection->getSelect()->where('second_table.is_variation_parent != ?',1);
        $collection->getSelect()->where('second_table.defected_messages IS NOT NULL');
        $collection->getSelect()->having('`api`.sku IS NULL');

        $tempColumns = array('main_table.id', 'api.sku');
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns($tempColumns);

        /** @var $stmtTemp Zend_Db_Statement_Pdo */
        $stmtTemp = $connWrite->query($collection->getSelect()->__toString());

        $notReceivedIds = array();
        while ($notReceivedItem = $stmtTemp->fetch()) {
            $notReceivedIds[] = (int)$notReceivedItem['id'];
        }
        $notReceivedIds = array_unique($notReceivedIds);

        if (empty($notReceivedIds)) {
            return;
        }

        $bind = array(
            'defected_messages' => null,
        );

        $chunkedIds = array_chunk($notReceivedIds,1000);
        foreach ($chunkedIds as $partIds) {
            $where = '`listing_product_id` IN ('.implode(',',$partIds).')';
            $connWrite->update(
                Mage::getResourceModel('M2ePro/Amazon_Listing_Product')->getMainTable(), $bind, $where
            );
        }
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getObjectByParam('Account','account_id');
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return $this->getAccount()->getChildObject()->getMarketplace();
    }

    // ---------------------------------------

    protected function getSynchronizationLog()
    {
        if (!is_null($this->synchronizationLog)) {
            return $this->synchronizationLog;
        }

        $this->synchronizationLog = Mage::getModel('M2ePro/Synchronization_Log');
        $this->synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);
        $this->synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_DEFAULTS);

        return $this->synchronizationLog;
    }

    //########################################
}