<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Sku
    extends Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Abstract
{
    //########################################

    /**
     * @return string
     */
    protected function getNick()
    {
        return '/sku/';
    }

    /**
     * @return string
     */
    protected function getTitle()
    {
        return 'Sku';
    }

    // ---------------------------------------

    /**
     * @return int
     */
    protected function getPercentsStart()
    {
        return 40;
    }

    /**
     * @return int
     */
    protected function getPercentsEnd()
    {
        return 50;
    }

    //########################################

    protected function performActions()
    {
        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');
        $accountsCollection->addFieldToFilter('other_listings_synchronization',
            Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_SYNCHRONIZATION_YES);

        $accounts = $accountsCollection->getItems();

        if (count($accounts) <= 0) {
            return;
        }

        $iteration = 0;
        $percentsForOneStep = ($this->getPercentsInterval()/2) / count($accounts);

        foreach ($accounts as $account) {

            /** @var $account Ess_M2ePro_Model_Account **/

            $this->getActualOperationHistory()->addTimePoint(
                __METHOD__.'process'.$account->getId(),
                'Get and process SKUs for Account '.$account->getTitle()
            );

            $this->updateSkus($account);

            $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());

            $offset = $this->getPercentsInterval() / 2 + $iteration * $percentsForOneStep;
            $this->getActualLockItem()->setPercents($offset);
            $this->getActualLockItem()->activate();

            $iteration++;
        }
    }

    //########################################

    private function updateSkus(Ess_M2ePro_Model_Account $account)
    {
        /** @var $listingOtherCollection Mage_Core_Model_Mysql4_Collection_Abstract */

        $listingOtherCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Other');
        $listingOtherCollection->addFieldToFilter('main_table.account_id',(int)$account->getId());
        $listingOtherCollection->getSelect()->where('`second_table`.`sku` IS NULL');
        $listingOtherCollection->getSelect()->order('second_table.start_date ASC');
        $listingOtherCollection->getSelect()->limit(200);

        if (!$listingOtherCollection->getSize()) {
            return;
        }

        $firstItem = $listingOtherCollection->getFirstItem();

        $sinceTime = $firstItem->getData('start_date');
        $receivedData = $this->receiveSkusFromEbay($account, $sinceTime);

        if (empty($receivedData['items'])) {
            foreach ($listingOtherCollection->getItems() as $listingOther) {
                $listingOther->getChildObject()->setData('sku','')->save();
            }
            return;
        }

        $this->updateSkusForReceivedItems($listingOtherCollection, $account, $receivedData['items']);
        $this->updateSkusForNotReceivedItems($listingOtherCollection, $receivedData['to_time']);
    }

    // ---------------------------------------

    private function updateSkusForReceivedItems($listingOtherCollection,Ess_M2ePro_Model_Account $account,array $items)
    {
        /** @var $mappingModel Ess_M2ePro_Model_Ebay_Listing_Other_Mapping */
        $mappingModel = Mage::getModel('M2ePro/Ebay_Listing_Other_Mapping');

        foreach ($items as $item) {
            foreach ($listingOtherCollection->getItems() as $listingOther) {

                /** @var $listingOther Ess_M2ePro_Model_Listing_Other */

                if ((float)$listingOther->getData('item_id') != $item['id']) {
                    continue;
                }

                $listingOther->getChildObject()->setData('sku',(string)$item['sku'])->save();

                if ($account->getChildObject()->isOtherListingsMappingEnabled()) {
                    $mappingModel->initialize($account);
                    $mappingModel->autoMapOtherListingProduct($listingOther);
                }

                break;
            }
        }
    }

    // eBay item IDs which were removed can lead to the issue and getting SKU process freezes
    private function updateSkusForNotReceivedItems($listingOtherCollection, $toTimeReceived)
    {
        foreach ($listingOtherCollection->getItems() as $listingOther) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Other $ebayListingOther */
            $ebayListingOther = $listingOther->getChildObject();

            if (!is_null($ebayListingOther->getSku())) {
                continue;
            }

            if (strtotime($ebayListingOther->getStartDate()) >= strtotime($toTimeReceived)) {
                continue;
            }

            $ebayListingOther->setData('sku', '')->save();
        }
    }

    //########################################

    private function receiveSkusFromEbay(Ess_M2ePro_Model_Account $account, $sinceTime)
    {
        $sinceTime = new DateTime($sinceTime,new DateTimeZone('UTC'));
        $sinceTime->modify('-1 minute');
        $sinceTime = $sinceTime->format('Y-m-d H:i:s');

        $inputData = array(
            'since_time'    => $sinceTime,
            'only_one_page' => true
        );

        $dispatcherObj = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher');
        $connectorObj = $dispatcherObj->getVirtualConnector(
            'item','get','all',
            $inputData,NULL,
            NULL,$account->getId(),NULL
        );

        $responseData = $dispatcherObj->process($connectorObj);

        if (!isset($responseData['items']) || !is_array($responseData['items'])) {
            return array();
        }

        return $responseData;
    }

    //########################################
}