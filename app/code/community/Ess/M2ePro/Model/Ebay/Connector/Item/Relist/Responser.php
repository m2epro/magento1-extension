<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Connector_Item_Relist_Responser
    extends Ess_M2ePro_Model_Ebay_Connector_Item_Responser
{
    //########################################

    protected function getSuccessfulMessage()
    {
        $currency = Mage::app()->getLocale()->currency(
            $this->_listingProduct->getMarketplace()->getChildObject()->getCurrency()
        );

        $onlineQty = $this->_listingProduct->getChildObject()->getOnlineQty() -
                     $this->_listingProduct->getChildObject()->getOnlineQtySold();

        if ($this->getRequestDataObject()->isVariationItem()) {
            $calculateWithEmptyQty = $this->_listingProduct->getChildObject()->isOutOfStockControlEnabled();

            return sprintf(
                'Product was Relisted with QTY %d, Price %s - %s',
                $onlineQty,
                $currency->toCurrency($this->getRequestDataObject()->getVariationMinPrice($calculateWithEmptyQty)),
                $currency->toCurrency($this->getRequestDataObject()->getVariationMaxPrice($calculateWithEmptyQty))
            );
        }

        return sprintf(
            'Product was Relisted with QTY %d, Price %s',
            $onlineQty,
            $currency->toCurrency($this->_listingProduct->getChildObject()->getOnlineCurrentPrice())
        );
    }

    //########################################

    protected function processCompleted(array $data = array(), array $params = array())
    {
        if (!empty($data['already_active'])) {
            $this->getResponseObject()->processAlreadyActive($data, $params);

            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromPreparedData(
                'Item was already started on eBay',
                Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_ERROR
            );

            $this->getLogger()->logListingProductMessage(
                $this->_listingProduct, $message
            );

            return;
        }

        parent::processCompleted($data, $params);
    }

    public function eventAfterExecuting()
    {
        $responseMessages = $this->getResponse()->getMessages()->getEntities();

        if (!$this->_listingProduct->getAccount()->getChildObject()->isModeSandbox() &&
            $this->isEbayApplicationErrorAppeared($responseMessages)) {
            $this->markAsPotentialDuplicate();

            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromPreparedData(
                'An error occurred while Listing the Item. The Item has been blocked.
                 The next M2E Pro Synchronization will resolve the problem.',
                Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_WARNING
            );

            $this->getLogger()->logListingProductMessage($this->_listingProduct, $message);
        }

        if ($this->isConditionErrorAppeared($responseMessages)) {
            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromPreparedData(
                Mage::helper('M2ePro')->__(
                    'M2E Pro was not able to send Condition on eBay. Please try to perform the Relist Action once more.'
                ),
                Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_WARNING
            );

            $this->getLogger()->logListingProductMessage($this->_listingProduct, $message);

            $additionalData = $this->_listingProduct->getAdditionalData();
            $additionalData['is_need_relist_condition'] = true;

            $this->_listingProduct
                ->setSettings('additional_data', $additionalData)
                ->save();
        }

        if ($this->getStatusChanger() == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_SYNCH &&
            $this->isItemCanNotBeAccessed($responseMessages)) {
            $itemId = null;
            if (isset($this->_params['product']['request']['item_id'])) {
                $itemId = $this->_params['product']['request']['item_id'];
            }

            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromPreparedData(
                Mage::helper('M2ePro')->__(
                    "This Item {$itemId} was not relisted as it cannot be accessed on eBay.
                    Instead, M2E Pro will run the List action based on your Synchronization Rules"
                ),
                Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_WARNING
            );

            $this->getLogger()->logListingProductMessage($this->_listingProduct, $message);

            $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');
            $this->processAdditionalAction(
                Ess_M2ePro_Model_Listing_Product::ACTION_LIST, $configurator,
                array('skip_check_the_same_product_already_listed_ids' => array($this->_listingProduct->getId()))
            );
        }

        if ($this->getStatusChanger() == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_SYNCH &&
            (!$this->getConfigurator()->isExcludingMode()) &&
            $this->isNewRequiredSpecificNeeded($responseMessages)) {
            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromPreparedData(
                'eBay Category assigned to this Item requires the Product Identifier to be specified
                (UPC, EAN, ISBN, etc.). The related data will be automatically submitted to the Channel based
                on eBay Catalog Identifiers settings in the Description Policy.',
                Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_WARNING
            );

            $this->getLogger()->logListingProductMessage($this->_listingProduct, $message);

            $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');
            $this->processAdditionalAction($this->getActionType(), $configurator);
        }

        $additionalData = $this->_listingProduct->getAdditionalData();

        if ($this->isVariationErrorAppeared($responseMessages) &&
            $this->getRequestDataObject()->hasVariations() &&
            !isset($additionalData['is_variation_mpn_filled'])
        ) {
            $this->tryToResolveVariationMpnErrors();
        }

        if ($message = $this->isDuplicateErrorByUUIDAppeared($responseMessages)) {
            $this->processDuplicateByUUID($message);
        }

        if ($message = $this->isDuplicateErrorByEbayEngineAppeared($responseMessages)) {
            $this->processDuplicateByEbayEngine($message);
        }

        parent::eventAfterExecuting();
    }

    //########################################
}