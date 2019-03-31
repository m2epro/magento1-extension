<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Walmart_Listing_Product_Channel_SynchronizeData_Blocked
    extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'walmart/listing/product/channel/synchronize_data/blocked';

    //####################################

    public function isPossibleToRun()
    {
        if (Mage::helper('M2ePro/Server_Maintenance')->isNow()) {
            return false;
        }

        return parent::isPossibleToRun();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Synchronization_Log
     */
    protected function getSynchronizationLog()
    {
        $synchronizationLog = parent::getSynchronizationLog();

        $synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Walmart::NICK);
        $synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_LISTINGS);

        return $synchronizationLog;
    }

    //########################################

    protected function performActions()
    {
        $accounts = Mage::helper('M2ePro/Component_Walmart')->getCollection('Account')->getItems();

        if (count($accounts) <= 0) {
            return;
        }

        foreach ($accounts as $account) {

            /** @var $account Ess_M2ePro_Model_Account **/

            $this->getOperationHistory()->addText('Starting Account "'.$account->getTitle().'"');

            $this->getOperationHistory()->addTimePoint(
                __METHOD__.'process'.$account->getId(),
                'Process Account '.$account->getTitle()
            );

            try {

                $this->processAccount($account);

            } catch (Exception $exception) {

                // M2ePro_TRANSLATIONS
                // The "Update Blocked Listings Products" Action for Walmart Account: "%account%" was completed with error.
                $message = 'The "Update Blocked Listings Products" Action for Walmart Account "%account%"';
                $message .= ' was completed with error.';
                $message = Mage::helper('M2ePro')->__($message, $account->getTitle());

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }

            $this->getOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());

            $this->getLockItemManager()->activate();
        }
    }

    //########################################

    private function processAccount(Ess_M2ePro_Model_Account $account)
    {
        /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Listing')->getCollection();
        $collection->addFieldToFilter('component_mode',Ess_M2ePro_Helper_Component_Walmart::NICK);
        $collection->addFieldToFilter('account_id',(int)$account->getId());

        if (!$collection->getSize()) {
            return;
        }

        $dispatcher = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');
        $connector = $dispatcher->getVirtualConnector('inventory', 'get', 'wpidsItems', array(), 'data', $account);
        $dispatcher->process($connector);

        $wpids = $connector->getResponseData();

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        /** @var $stmtTemp Zend_Db_Statement_Pdo */
        $stmtTemp = $connRead->query($this->getPdoStatementExistingListings($account));

        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode(Ess_M2ePro_Helper_Component_Walmart::NICK);

        $logsActionId = Mage::getResourceModel('M2ePro/Listing_Log')->getNextActionId();

        $notReceivedIds = array();
        while ($existingItem = $stmtTemp->fetch()) {

            if (in_array($existingItem['wpid'], $wpids)) {
                continue;
            }

            $notReceivedItem = $existingItem;

            if (!in_array((int)$notReceivedItem['id'],$notReceivedIds)) {
                $statusChangedFrom = Mage::helper('M2ePro/Component_Walmart')
                    ->getHumanTitleByListingProductStatus($notReceivedItem['status']);
                $statusChangedTo = Mage::helper('M2ePro/Component_Walmart')
                    ->getHumanTitleByListingProductStatus(Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED);

                // M2ePro_TRANSLATIONS
                // Item Status was successfully changed from "%from%" to "%to%" .
                $tempLogMessage = Mage::helper('M2ePro')->__(
                    'Item Status was successfully changed from "%from%" to "%to%" .',
                    $statusChangedFrom,
                    $statusChangedTo
                );

                $tempLog->addProductMessage(
                    $notReceivedItem['listing_id'],
                    $notReceivedItem['product_id'],
                    $notReceivedItem['id'],
                    Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                    $logsActionId,
                    Ess_M2ePro_Model_Listing_Log::ACTION_CHANNEL_CHANGE,
                    $tempLogMessage,
                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
                );

                if (!empty($notReceivedItem['is_variation_product']) &&
                    !empty($notReceivedItem['variation_parent_id'])
                ) {
                    $parentIdsForProcessing[] = $notReceivedItem['variation_parent_id'];
                }
            }

            $notReceivedIds[] = (int)$notReceivedItem['id'];
        }
        $notReceivedIds = array_unique($notReceivedIds);

        $mainBind = array(
            'status'         => Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED,
            'status_changer' => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT,
        );

        $childBind = array(
            'is_missed_on_channel' => 1,
        );

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $listingProductMainTable = Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable();
        $listingProductChildTable = Mage::getResourceModel('M2ePro/Walmart_Listing_Product')->getMainTable();

        $chunckedIds = array_chunk($notReceivedIds,1000);
        foreach ($chunckedIds as $partIds) {
            $where = '`id` IN ('.implode(',',$partIds).')';
            $connWrite->update($listingProductMainTable,$mainBind,$where);

            $where = '`listing_product_id` IN ('.implode(',',$partIds).')';
            $connWrite->update($listingProductChildTable,$childBind,$where);
        }

        if (!empty($parentIdsForProcessing)) {
            $this->processParentProcessors($parentIdsForProcessing);
        }
    }

    protected function getPdoStatementExistingListings(Ess_M2ePro_Model_Account $account)
    {
        /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $collection->getSelect()->join(
            array('l' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
            'main_table.listing_id = l.id',
            array()
        );

        $collection->addFieldToFilter('l.account_id', (int)$account->getId());
        $collection->addFieldToFilter('status',array('nin' => array(
            Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED,
            Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED
        )));
        $collection->addFieldToFilter('is_variation_parent', array('neq' => 1));
        $collection->addFieldToFilter('is_missed_on_channel', array('neq' => 1));

        /**
         * Wait for 24 hours before the newly listed item can be marked as inactive blocked
         */
        $borderDate = new DateTime('now', new \DateTimeZone('UTC'));
        $borderDate->modify('- 24 hours');
        $collection->addFieldToFilter(
            new \Zend_Db_Expr('list_date IS NULL OR list_date'), array('lt' => $borderDate->format('Y-m-d H:i:s'))
        );

        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                 'main_table.id',
                 'main_table.status',
                 'main_table.listing_id',
                 'main_table.product_id',
                 'second_table.wpid',
                 'second_table.is_variation_product',
                 'second_table.variation_parent_id'
            ));

        return $collection->getSelect()->__toString();
    }

    //########################################

    protected function processParentProcessors(array $parentIds)
    {
        if (empty($parentIds)) {
            return;
        }

        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $parentListingProductCollection */
        $parentListingProductCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $parentListingProductCollection->addFieldToFilter('id', array('in' => array_unique($parentIds)));

        $parentListingsProducts = $parentListingProductCollection->getItems();
        if (empty($parentListingsProducts)) {
            return;
        }

        $massProcessor = Mage::getModel(
            'M2ePro/Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Mass'
        );
        $massProcessor->setListingsProducts($parentListingsProducts);
        $massProcessor->setForceExecuting(false);

        $massProcessor->execute();
    }

    //########################################
}