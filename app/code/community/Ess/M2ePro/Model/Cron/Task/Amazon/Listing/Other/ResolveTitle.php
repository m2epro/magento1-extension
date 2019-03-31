<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Other_ResolveTitle extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'amazon/listing/other/resolve_title';

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

        $synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);
        $synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_OTHER_LISTINGS);

        return $synchronizationLog;
    }

    //########################################

    protected function performActions()
    {
        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account');
        $accountsCollection->addFieldToFilter('other_listings_synchronization',
                                              Ess_M2ePro_Model_Amazon_Account::OTHER_LISTINGS_SYNCHRONIZATION_YES);

        $accounts = $accountsCollection->getItems();

        if (count($accounts) <= 0) {
            return;
        }

        foreach ($accounts as $account) {

            /** @var $account Ess_M2ePro_Model_Account **/

            $this->getOperationHistory()->addTimePoint(
                __METHOD__.'process'.$account->getId(),
                'Get and process Titles for Account '.$account->getTitle()
            );

            try {

                $this->updateTitlesByAsins($account);

            } catch (Exception $exception) {

                $message = Mage::helper('M2ePro')->__(
                    'The "Update Titles" Action for Amazon Account "%account%" was completed with error.',
                    $account->getTitle()
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }

            $this->getOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());

            $this->getLockItemManager()->activate();
        }
    }

    //########################################

    private function updateTitlesByAsins(Ess_M2ePro_Model_Account $account)
    {
        for ($i = 0; $i <= 5; $i++) {

            /** @var $listingOtherCollection Mage_Core_Model_Mysql4_Collection_Abstract */

            $listingOtherCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Other');
            $listingOtherCollection->addFieldToFilter('main_table.account_id', (int)$account->getId());
            $listingOtherCollection->getSelect()->where('`second_table`.`title` IS NULL');
            $listingOtherCollection->getSelect()->order('main_table.id ASC');
            $listingOtherCollection->getSelect()->limit(5);

            if (!$listingOtherCollection->getSize()) {
                return;
            }

            $neededItems = array();
            foreach ($listingOtherCollection->getItems() as $tempItem) {
                $neededItems[] = $tempItem->getData('general_id');
            }

            $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector(
                'product', 'search', 'byIdentifiers',
                array(
                    'items'         => $neededItems,
                    'id_type'       => 'ASIN',
                    'only_realtime' => 1
                ), NULL,
                $account->getId()
            );

            $dispatcherObject->process($connectorObj);
            $responseData = $connectorObj->getResponseData();

            if (!empty($responseData['unavailable']) && $responseData['unavailable'] == true) {
                return;
            }

            $this->updateReceivedTitles($responseData, $account);
            $this->updateNotReceivedTitles($neededItems, $responseData);
        }
    }

    // ---------------------------------------

    private function updateReceivedTitles(array $responseData, Ess_M2ePro_Model_Account $account)
    {
        if (!isset($responseData['items']) || !is_array($responseData['items'])) {
            return;
        }

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $aloTable = Mage::getResourceModel('M2ePro/Amazon_Listing_Other')->getMainTable();
        $lolTable = Mage::getResourceModel('M2ePro/Listing_Other_Log')->getMainTable();

        /** @var $mappingModel Ess_M2ePro_Model_Amazon_Listing_Other_Mapping */
        $mappingModel = Mage::getModel('M2ePro/Amazon_Listing_Other_Mapping');

        $receivedItems = array();
        foreach ($responseData['items'] as $generalId => $item) {

            if ($item == false) {
                continue;
            }

            $item = array_shift($item);
            $title = $item['title'];

            if (isset($receivedItems[$generalId]) || empty($title)) {
                continue;
            }

            $receivedItems[$generalId] = $title;

            $listingsOthersWithEmptyTitles = array();
            if ($account->getChildObject()->isOtherListingsMappingEnabled()) {

                /** @var $listingOtherCollection Mage_Core_Model_Mysql4_Collection_Abstract */
                $listingOtherCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Other')
                                              ->addFieldToFilter('main_table.account_id',(int)$account->getId())
                                              ->addFieldToFilter('second_table.general_id',(int)$generalId)
                                              ->addFieldToFilter('second_table.title',array('null' => true));

                $listingsOthersWithEmptyTitles = $listingOtherCollection->getItems();
            }

            $connWrite->update(
                $aloTable,
                array('title' => (string)$title),
                array('general_id = ?' => (string)$generalId)
            );

            $connWrite->update(
                $lolTable,
                array('title' => (string)$title),
                array(
                    'identifier = ?' => (string)$generalId,
                    'component_mode = ?' => Ess_M2ePro_Helper_Component_Amazon::NICK
                )
            );

            if (count($listingsOthersWithEmptyTitles) > 0) {

                foreach ($listingsOthersWithEmptyTitles as $listingOtherModel) {

                    $listingOtherModel->setData('title',(string)$title);
                    $listingOtherModel->getChildObject()->setData('title',(string)$title);

                    $mappingModel->initialize($account);
                    $mappingModel->autoMapOtherListingProduct($listingOtherModel);
                }
            }
        }
    }

    private function updateNotReceivedTitles($neededItems, $responseData) {

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $aloTable = Mage::getResourceModel('M2ePro/Amazon_Listing_Other')->getMainTable();

        foreach ($neededItems as $generalId) {

            if (isset($responseData['items'][$generalId]) &&
                !empty($responseData['items'][$generalId][0]['title'])) {
                continue;
            }

            $connWrite->update(
                $aloTable,
                array('title' => Ess_M2ePro_Model_Amazon_Listing_Other::EMPTY_TITLE_PLACEHOLDER),
                array('general_id = ?' => (string)$generalId)
            );
        }
    }

    //########################################
}