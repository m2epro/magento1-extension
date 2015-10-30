<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Defaults_UpdateListingsProducts
    extends Ess_M2ePro_Model_Ebay_Synchronization_Defaults_Abstract
{
    const EBAY_STATUS_ACTIVE = 'Active';
    const EBAY_STATUS_ENDED = 'Ended';
    const EBAY_STATUS_COMPLETED = 'Completed';

    private $logsActionId = NULL;

    //########################################

    /**
     * @return string
     */
    protected function getNick()
    {
        return '/update_listings_products/';
    }

    /**
     * @return string
     */
    protected function getTitle()
    {
        return 'Update Listings Products';
    }

    // ---------------------------------------

    /**
     * @return int
     */
    protected function getPercentsStart()
    {
        return 20;
    }

    /**
     * @return int
     */
    protected function getPercentsEnd()
    {
        return 80;
    }

    //########################################

    protected function performActions()
    {
        $accounts = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account')->getItems();

        if (count($accounts) <= 0) {
            return;
        }

        $iteration = 0;
        $percentsForOneStep = $this->getPercentsInterval() / count($accounts);

        foreach ($accounts as $account) {

            /** @var $account Ess_M2ePro_Model_Account **/

            $this->getActualOperationHistory()->addText('Starting Account "'.$account->getTitle().'"');
            // M2ePro_TRANSLATIONS
            // The "Update Listings Products" Action for eBay Account: "%account_title%" is started. Please wait...
            $status = 'The "Update Listings Products" Action for eBay Account: "%account_title%" is started. ';
            $status .= 'Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));

            $this->getActualOperationHistory()->addTimePoint(
                __METHOD__.'process'.$account->getId(),
                'Process Account '.$account->getTitle()
            );

            $this->processAccount($account);

            $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());

            // M2ePro_TRANSLATIONS
            // The "Update Listings Products" Action for eBay Account: "%account_title%" is finished. Please wait...
            $status = 'The "Update Listings Products" Action for eBay Account: "%account_title%" is finished.'.
                ' Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));
            $this->getActualLockItem()->setPercents($this->getPercentsStart() + $iteration * $percentsForOneStep);
            $this->getActualLockItem()->activate();

            $iteration++;
        }
    }

    // ---------------------------------------

    private function processAccount(Ess_M2ePro_Model_Account $account)
    {
        $sinceTime = $this->prepareSinceTime($account->getData('defaults_last_synchronization'));
        $changesByAccount = $this->getChangesByAccount($account, $sinceTime);

        if (!isset($changesByAccount['items']) || !isset($changesByAccount['to_time'])) {
            return;
        }

        $account->getChildObject()->setData('defaults_last_synchronization', $changesByAccount['to_time'])->save();

        Mage::helper('M2ePro/Data_Cache_Session')->setValue(
            'item_get_changes_data_' . $account->getId(), $changesByAccount
        );

        foreach ($changesByAccount['items'] as $change) {

            /* @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $listingProduct = Mage::helper('M2ePro/Component_Ebay')->getListingProductByEbayItem(
                $change['id'], $account->getId()
            );

            if (is_null($listingProduct)) {
                continue;
            }

            // Listing product isn't listed and it child must have another item_id
            if ($listingProduct->getStatus() != Ess_M2ePro_Model_Listing_Product::STATUS_LISTED &&
                $listingProduct->getStatus() != Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN) {
                continue;
            }

            $this->processListingProduct($listingProduct,$change);

            if (empty($change['variations'])) {
                continue;
            }

            $variations = $listingProduct->getVariations(true);

            if (count($variations) <= 0) {
                continue;
            }

            $variationsSnapshot = $this->getVariationsSnapshot($variations);

            if (count($variationsSnapshot) <= 0) {
                return;
            }

            $this->processListingProductVariation($variationsSnapshot,$change['variations'], $listingProduct);
        }
    }

    private function processListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct, array $change)
    {
        $oldStatus = $listingProduct->getStatus();

        $updateData = array_merge(
            $this->getProductPriceChanges($listingProduct, $change),
            $this->getProductQtyChanges($listingProduct, $change),
            $this->getProductDatesChanges($listingProduct, $change),
            $this->getProductStatusChanges($listingProduct, $change)
        );

        $listingProduct->addData($updateData)->save();

        if ($oldStatus !== $updateData['status']) {
            $listingProduct->getChildObject()->updateVariationsStatus();
        }
    }

    private function processListingProductVariation(array $variationsSnapshot,
                                                    array $changeVariations,
                                                    Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        foreach ($changeVariations as $changeVariation) {
            foreach ($variationsSnapshot as $variationSnapshot) {

                if (!$this->isVariationEqualWithChange($changeVariation,$variationSnapshot)) {
                    continue;
                }

                $updateData = array(
                    'online_price' => (float)$changeVariation['price'] < 0 ? 0 : (float)$changeVariation['price'],
                    'online_qty' => (int)$changeVariation['quantity'] < 0 ? 0 : (int)$changeVariation['quantity'],
                    'online_qty_sold' => (int)$changeVariation['quantitySold'] < 0 ?
                                                                0 : (int)$changeVariation['quantitySold']
                );

                /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Variation $ebayVariationObj */
                $ebayVariationObj = $variationSnapshot['variation']->getChildObject();

                if ($ebayVariationObj->getOnlinePrice() != $updateData['online_price'] ||
                    $ebayVariationObj->getOnlineQty() != $updateData['online_qty'] ||
                    $ebayVariationObj->getOnlineQtySold() != $updateData['online_qty_sold']) {

                    $variationSnapshot['variation']->addData($updateData)->save();
                    $variationSnapshot['variation']->getChildObject()->setStatus($listingProduct->getStatus());
                }
                break;
            }
        }
    }

    //########################################

    private function getChangesByAccount(Ess_M2ePro_Model_Account $account, $sinceTime)
    {
        $nextSinceTime = new DateTime($sinceTime, new DateTimeZone('UTC'));

        $response = $this->receiveChangesFromEbay(
            $account, array('since_time' => $nextSinceTime->format('Y-m-d H:i:s'))
        );

        if ($response) {
            return (array)$response;
        }

        $previousSinceTime = $nextSinceTime;

        $nextSinceTime = new DateTime('now', new DateTimeZone('UTC'));
        $nextSinceTime->modify("-1 day");

        if ($previousSinceTime->format('U') < $nextSinceTime->format('U')) {

            // from day behind now
            $response = $this->receiveChangesFromEbay(
                $account, array('since_time' => $nextSinceTime->format('Y-m-d H:i:s'))
            );

            if ($response) {
                return (array)$response;
            }

            $previousSinceTime = $nextSinceTime;
        }

        $nextSinceTime = new DateTime('now', new DateTimeZone('UTC'));

        if ($previousSinceTime->format('U') < $nextSinceTime->format('U')) {

            // from now
            $response = $this->receiveChangesFromEbay(
                $account, array('since_time' => $nextSinceTime->format('Y-m-d H:i:s'))
            );

            if ($response) {
                return (array)$response;
            }
        }

        return array();
    }

    private function receiveChangesFromEbay(Ess_M2ePro_Model_Account $account, array $paramsConnector = array())
    {
        $dispatcherObj = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher');
        $connectorObj = $dispatcherObj->getVirtualConnector('item','get','changes',
                                                            $paramsConnector,NULL,
                                                            NULL,$account->getId(),NULL);

        $response = $dispatcherObj->process($connectorObj);
        $this->processResponseMessages($connectorObj);

        if (!isset($response['items']) || !isset($response['to_time'])) {
            return NULL;
        }

        return $response;
    }

    private function processResponseMessages(Ess_M2ePro_Model_Connector_Protocol $connectorObj)
    {
        foreach ($connectorObj->getErrorMessages() as $message) {

            if ($message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_CODE_KEY] == 21917062) {
                continue;
            }

            if (!$connectorObj->isMessageError($message) && !$connectorObj->isMessageWarning($message)) {
                continue;
            }

            $logType = $connectorObj->isMessageError($message) ? Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
                                                               : Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;

            $this->getLog()->addMessage(
                Mage::helper('M2ePro')->__($message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY]),
                $logType,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );
        }
    }

    //########################################

    private function getProductPriceChanges(Ess_M2ePro_Model_Listing_Product $listingProduct, array $change)
    {
        $data = array();

        $data['online_current_price'] = (float)$change['currentPrice'] < 0 ? 0 : (float)$change['currentPrice'];

        $listingType = $this->getActualListingType($listingProduct, $change);

        if ($listingType == Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_FIXED) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
            $ebayListingProduct = $listingProduct->getChildObject();

            if ($ebayListingProduct->getOnlineCurrentPrice() != $data['online_current_price']) {
                // M2ePro_TRANSLATIONS
                // Item Price was successfully changed from %from% to %to% .
                $this->logReportChange($listingProduct, Mage::helper('M2ePro')->__(
                    'Item Price was successfully changed from %from% to %to% .',
                    $ebayListingProduct->getOnlineCurrentPrice(),
                    $data['online_current_price']
                ));

                Mage::getModel('M2ePro/ProductChange')->addUpdateAction(
                    $listingProduct->getProductId(), Ess_M2ePro_Model_ProductChange::INITIATOR_SYNCHRONIZATION
                );
            }
        }

        return $data;
    }

    private function getProductQtyChanges(Ess_M2ePro_Model_Listing_Product $listingProduct, array $change)
    {
        $data = array();

        $data['online_qty'] = (int)$change['quantity'] < 0 ? 0 : (int)$change['quantity'];
        $data['online_qty_sold'] = (int)$change['quantitySold'] < 0 ? 0 : (int)$change['quantitySold'];

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        $listingType = $this->getActualListingType($listingProduct, $change);

        if ($listingType == Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION) {
            $data['online_qty'] = 1;
            $data['online_bids'] = (int)$change['bidCount'] < 0 ? 0 : (int)$change['bidCount'];
        }

        if ($ebayListingProduct->getOnlineQty() != $data['online_qty'] ||
            $ebayListingProduct->getOnlineQtySold() != $data['online_qty_sold']) {
            // M2ePro_TRANSLATIONS
            // Item QTY was successfully changed from %from% to %to% .
            $this->logReportChange($listingProduct, Mage::helper('M2ePro')->__(
                'Item QTY was successfully changed from %from% to %to% .',
                ($ebayListingProduct->getOnlineQty() - $ebayListingProduct->getOnlineQtySold()),
                ($data['online_qty'] - $data['online_qty_sold'])
            ));

            Mage::getModel('M2ePro/ProductChange')->addUpdateAction(
                $listingProduct->getProductId(), Ess_M2ePro_Model_ProductChange::INITIATOR_SYNCHRONIZATION
            );
        }

        return $data;
    }

    private function getProductDatesChanges(Ess_M2ePro_Model_Listing_Product $listingProduct, array $change)
    {
        return array(
            'start_date' => Ess_M2ePro_Model_Connector_Ebay_Abstract::ebayTimeToString($change['startTime']),
            'end_date' => Ess_M2ePro_Model_Connector_Ebay_Abstract::ebayTimeToString($change['endTime'])
        );
    }

    // ---------------------------------------

    private function getProductStatusChanges(Ess_M2ePro_Model_Listing_Product $listingProduct, array $change)
    {
        $data = array();

        $qty = (int)$change['quantity'] < 0 ? 0 : (int)$change['quantity'];
        $qtySold = (int)$change['quantitySold'] < 0 ? 0 : (int)$change['quantitySold'];

        if (($change['listingStatus'] == self::EBAY_STATUS_COMPLETED ||
             $change['listingStatus'] == self::EBAY_STATUS_ENDED) &&
             $qty == $qtySold) {

            $data['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_SOLD;

        } else if ($change['listingStatus'] == self::EBAY_STATUS_COMPLETED) {

            $data['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;

        } else if ($change['listingStatus'] == self::EBAY_STATUS_ENDED) {

            $data['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED;

        } else if ($change['listingStatus'] == self::EBAY_STATUS_ACTIVE &&
                   $qty - $qtySold <= 0) {

            $data['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN;

        } else if ($change['listingStatus'] == self::EBAY_STATUS_ACTIVE) {

            $data['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
        }

        if ($listingProduct->getStatus() == $data['status']) {
            return $data;
        }

        $data['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;

        $statusChangedFrom = Mage::helper('M2ePro/Component_Ebay')
            ->getHumanTitleByListingProductStatus($listingProduct->getStatus());
        $statusChangedTo = Mage::helper('M2ePro/Component_Ebay')
            ->getHumanTitleByListingProductStatus($data['status']);

        if (!empty($statusChangedFrom) && !empty($statusChangedTo)) {
            // M2ePro_TRANSLATIONS
            // Item Status was successfully changed from "%from%" to "%to%" .
            $this->logReportChange($listingProduct, Mage::helper('M2ePro')->__(
                'Item Status was successfully changed from "%from%" to "%to%" .',
                $statusChangedFrom,
                $statusChangedTo
            ));
        }

        Mage::getModel('M2ePro/ProductChange')->addUpdateAction(
            $listingProduct->getProductId(), Ess_M2ePro_Model_ProductChange::INITIATOR_SYNCHRONIZATION
        );

        return $data;
    }

    //########################################

    private function getVariationsSnapshot(array $variations)
    {
        $snapshot = array();

        foreach ($variations as $variation) {

            /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */

            $options = $variation->getOptions(true);

            if (count($options) <= 0) {
                continue;
            }

            $snapshot[] = array(
                'variation' => $variation,
                'options' => $options
            );
        }

        return $snapshot;
    }

    private function isVariationEqualWithChange(array $changeVariation, array $variationSnapshot)
    {
        if (count($variationSnapshot['options']) != count($changeVariation['specifics'])) {
            return false;
        }

        foreach ($variationSnapshot['options'] as $variationSnapshotOption) {

            $haveOption = false;

            foreach ($changeVariation['specifics'] as $changeVariationOption=>$changeVariationValue) {

                if ($variationSnapshotOption->getData('attribute') == $changeVariationOption &&
                    $variationSnapshotOption->getData('option') == $changeVariationValue) {
                    $haveOption = true;
                    break;
                }
            }

            if ($haveOption === false) {
                return false;
            }
        }

        return true;
    }

    //########################################

    private function prepareSinceTime($sinceTime)
    {
        $minTime = new DateTime('now', new DateTimeZone('UTC'));
        $minTime->modify("-1 month");

        if (empty($sinceTime) || strtotime($sinceTime) < (int)$minTime->format('U')) {
            $sinceTime = new DateTime('now', new DateTimeZone('UTC'));
            $sinceTime = $sinceTime->format('Y-m-d H:i:s');
        }

        return $sinceTime;
    }

    // ---------------------------------------

    private function getLogsActionId()
    {
        if (is_null($this->logsActionId)) {
            $this->logsActionId = Mage::getModel('M2ePro/Listing_Log')->getNextActionId();
        }
        return $this->logsActionId;
    }

    private function getActualListingType(Ess_M2ePro_Model_Listing_Product $listingProduct, array $change)
    {
        $validEbayValues = array(
            Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Selling::LISTING_TYPE_AUCTION,
            Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Selling::LISTING_TYPE_FIXED
        );

        if (isset($change['listingType']) && in_array($change['listingType'],$validEbayValues)) {

            switch ($change['listingType']) {
                case Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Selling::LISTING_TYPE_AUCTION:
                    $result = Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION;
                    break;
                case Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Selling::LISTING_TYPE_FIXED:
                    $result = Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_FIXED;
                    break;
            }

        } else {
            $result = $listingProduct->getChildObject()->getListingType();
        }

        return $result;
    }

    //########################################

    private function logReportChange(Ess_M2ePro_Model_Listing_Product $listingProduct, $logMessage)
    {
        if (empty($logMessage)) {
            return;
        }

        $log = Mage::getModel('M2ePro/Listing_Log');
        $log->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

        $log->addProductMessage(
            $listingProduct->getListingId(),
            $listingProduct->getProductId(),
            $listingProduct->getId(),
            Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
            $this->getLogsActionId(),
            Ess_M2ePro_Model_Listing_Log::ACTION_CHANNEL_CHANGE,
            $logMessage,
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
        );
    }

    //########################################
}