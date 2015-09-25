<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Buy_Synchronization_OtherListings_Title
    extends Ess_M2ePro_Model_Buy_Synchronization_OtherListings_Abstract
{
    //####################################

    protected function getNick()
    {
        return '/title/';
    }

    protected function getTitle()
    {
        return 'Title';
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 90;
    }

    protected function getPercentsEnd()
    {
        return 100;
    }

    //####################################

    protected function performActions()
    {
        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Buy')->getCollection('Account');
        $accountsCollection->addFieldToFilter('other_listings_synchronization',
            Ess_M2ePro_Model_Buy_Account::OTHER_LISTINGS_SYNCHRONIZATION_YES);

        $accounts = $accountsCollection->getItems();

        if (count($accounts) <= 0) {
            return;
        }

        $iteration = 0;
        $percentsForOneStep = ($this->getPercentsInterval() / 2) / count($accounts);

        foreach ($accounts as $account) {

            /** @var $account Ess_M2ePro_Model_Account **/

            if (!is_null($account->getChildObject()->getOtherListingsFirstSynchronization())) {

                $this->getActualOperationHistory()->addTimePoint(
                    __METHOD__.'process'.$account->getId(),
                    'Get and process Titles for Account '.$account->getTitle()
                );

                $this->isNeedUpdateTitlesByPages($account) ? $this->updateTitlesByPages($account) :
                                                             $this->updateTitlesBySkus($account);

                $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());
            }

            $offset = $this->getPercentsInterval() / 2 + $iteration * $percentsForOneStep;
            $this->getActualLockItem()->setPercents($offset);
            $this->getActualLockItem()->activate();

            $iteration++;
        }
    }

    //------------------------------------

    private function isNeedUpdateTitlesByPages(Ess_M2ePro_Model_Account $account)
    {
        $settings = $account->getChildObject()->getDecodedOtherListingsUpdateTitlesSettings();

        if (is_null($settings)) {
            return true;
        }

        if ((int)$settings['next_status'] <= 2 && (int)$settings['next_page'] < 10000) {
            return true;
        }

        return false;
    }

    //####################################

    private function updateTitlesByPages(Ess_M2ePro_Model_Account $account)
    {
        $inputData = array(
            'necessary_status' => 0,
            'necessary_page' => 1
        );

        $settings = $account->getChildObject()->getDecodedOtherListingsUpdateTitlesSettings();

        if (!is_null($settings)) {
            $inputData['necessary_status'] = (int)$settings['next_status'];
            $inputData['necessary_page'] = (int)$settings['next_page'];
        }

        $dispatcherObject = Mage::getModel('M2ePro/Connector_Buy_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector(
            'inventory','get','pagesTitles',
            $inputData,NULL,
            $account->getId()
        );

        $responseData = $dispatcherObject->process($connectorObj);

        $this->updateReceivedTitles($responseData, $account);
        $pagesSettings = $this->calculateNextPagesSettings($responseData, $inputData);

        $account->getChildObject()
                ->setData('other_listings_update_titles_settings',$pagesSettings)
                ->save();
    }

    //------------------------------------

    private function calculateNextPagesSettings($responseData, $inputData)
    {
        $nextStatus = (int)$inputData['necessary_status'];
        $nextPage = (int)$inputData['necessary_page'] + (int)$responseData['processed_pages'];

        if ((bool)$responseData['is_last_page']) {

            if ($nextStatus >= 2) {
                $nextPage = 10000;
            } else {
                $nextStatus++;
                $nextPage = 1;
            }
        }

        return json_encode(array(
            'next_status' => $nextStatus,
            'next_page'   => $nextPage
        ));
    }

    //####################################

    private function updateTitlesBySkus(Ess_M2ePro_Model_Account $account)
    {
        /** @var $listingOtherCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $listingOtherCollection = Mage::helper('M2ePro/Component_Buy')->getCollection('Listing_Other');
        $listingOtherCollection->addFieldToFilter('`main_table`.account_id',(int)$account->getId());
        $listingOtherCollection->getSelect()->where('`second_table`.`title` IS NULL');
        $listingOtherCollection->getSelect()->order('main_table.create_date ASC');
        $listingOtherCollection->getSelect()->limit(10);

        if (!$listingOtherCollection->getSize()) {
            return;
        }

        $neededItems = array();
        foreach ($listingOtherCollection->getItems() as $tempItem) {
            $neededItems[] = $tempItem->getData('general_id');
        }

        $dispatcherObject = Mage::getModel('M2ePro/Connector_Buy_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector(
            'inventory','get','skusTitles',
            array('items'=>$neededItems),NULL,
            $account->getId()
        );

        $responseData = $dispatcherObject->process($connectorObj);

        $this->updateReceivedTitles($responseData, $account);
        $this->updateNotReceivedTitles($neededItems, $responseData);
    }

    //------------------------------------

    private function updateReceivedTitles(array $responseData, Ess_M2ePro_Model_Account $account)
    {
        if (!isset($responseData['items']) || !is_array($responseData['items'])) {
            return;
        }

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $bloTable = Mage::getResourceModel('M2ePro/Buy_Listing_Other')->getMainTable();
        $lolTable = Mage::getResourceModel('M2ePro/Listing_Other_Log')->getMainTable();

        /** @var $mappingModel Ess_M2ePro_Model_Buy_Listing_Other_Mapping */
        $mappingModel = Mage::getModel('M2ePro/Buy_Listing_Other_Mapping');

        /** @var $movingModel Ess_M2ePro_Model_Buy_Listing_Other_Moving */
        $movingModel = Mage::getModel('M2ePro/Buy_Listing_Other_Moving');

        $receivedItems = array();
        foreach ($responseData['items'] as $generalId => $title) {

            if (isset($receivedItems[$generalId]) || empty($title)) {
                continue;
            }

            $receivedItems[$generalId] = $title;

            $listingsOthersWithEmptyTitles = array();
            if ($account->getChildObject()->isOtherListingsMappingEnabled()) {

                /** @var $listingOtherCollection Mage_Core_Model_Mysql4_Collection_Abstract */
                $listingOtherCollection = Mage::helper('M2ePro/Component_Buy')->getCollection('Listing_Other')
                    ->addFieldToFilter('`main_table`.account_id',(int)$account->getId())
                    ->addFieldToFilter('`second_table`.`general_id`',(int)$generalId)
                    ->addFieldToFilter('`second_table`.`title`',array('null' => true));

                $listingsOthersWithEmptyTitles = $listingOtherCollection->getItems();
            }

            $connWrite->update(
                $bloTable,
                array('title' => (string)$title),
                array('general_id = ?' => (int)$generalId)
            );

            $connWrite->update(
                $lolTable,
                array('title' => (string)$title),
                array(
                    'identifier = ?' => (int)$generalId,
                    'component_mode = ?' => Ess_M2ePro_Helper_Component_Buy::NICK
                )
            );

            if (count($listingsOthersWithEmptyTitles) > 0) {

                foreach ($listingsOthersWithEmptyTitles as $listingOtherModel) {

                    $listingOtherModel->setData('title',(string)$title);
                    $listingOtherModel->getChildObject()->setData('title',(string)$title);

                    $mappingModel->initialize($account);
                    $mappingResult = $mappingModel->autoMapOtherListingProduct($listingOtherModel);

                    if ($mappingResult) {

                        if (!$account->getChildObject()->isOtherListingsMoveToListingsEnabled()) {
                            continue;
                        }

                        $movingModel->initialize($account);
                        $movingModel->autoMoveOtherListingProduct($listingOtherModel);
                    }
                }
            }
        }
    }

    private function updateNotReceivedTitles($neededItems, $responseData) {

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $bloTable = Mage::getResourceModel('M2ePro/Buy_Listing_Other')->getMainTable();

        foreach ($neededItems as $generalId) {

            if (isset($responseData['items'][$generalId]) &&
                !empty($responseData['items'][$generalId])) {
                continue;
            }

            $connWrite->update(
                $bloTable,
                array('title' => ''),
                array('general_id = ?' => (int)$generalId)
            );
        }
    }

    //####################################
}