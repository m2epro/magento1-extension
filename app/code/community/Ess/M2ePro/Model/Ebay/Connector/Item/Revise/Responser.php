<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Connector_Item_Revise_Responser
    extends Ess_M2ePro_Model_Ebay_Connector_Item_Responser
{
    //########################################

    /**
     * @return string
     */
    protected function getSuccessfulMessage()
    {
        if ($this->getConfigurator()->isExcludingMode()) {
            return 'Item was Revised';
        }

        $sequenceStrings = array();
        $isPlural = false;

        if ($this->getConfigurator()->isTitleAllowed()) {
            $sequenceStrings[] = 'Title';
        }

        if ($this->getConfigurator()->isSubtitleAllowed()) {
            $sequenceStrings[] = 'Subtitle';
        }

        if ($this->getConfigurator()->isDescriptionAllowed()) {
            $sequenceStrings[] = 'Description';
        }

        if ($this->getConfigurator()->isImagesAllowed()) {
            $sequenceStrings[] = 'Images';
            $isPlural = true;
        }

        if ($this->getConfigurator()->isCategoriesAllowed()) {
            $sequenceStrings[] = 'Categories / Specifics';
            $isPlural = true;
        }

        if ($this->getConfigurator()->isPaymentAllowed()) {
            $sequenceStrings[] = 'Payment';
        }

        if ($this->getConfigurator()->isShippingAllowed()) {
            $sequenceStrings[] = 'Shipping';
        }

        if ($this->getConfigurator()->isReturnAllowed()) {
            $sequenceStrings[] = 'Return';
        }

        if ($this->getConfigurator()->isOtherAllowed()) {
            $sequenceStrings[] = 'Condition, Condition Note, Lot Size, Tax, Best Offer, Donation';
            $isPlural = true;
        }

        if (empty($sequenceStrings)) {
            return null;
        }

        if (count($sequenceStrings) == 1) {
            $verb = $isPlural ? 'were' : 'was';
            return $sequenceStrings[0].' '.$verb.' Revised';
        }

        return implode(', ', $sequenceStrings).' were Revised';
    }

    //########################################

    protected function processCompleted(array $data = array(), array $params = array())
    {
        if (!empty($data['already_stop'])) {
            $this->getResponseObject()->processAlreadyStopped($data, $params);

            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromPreparedData(
                'Item was already Stopped on eBay',
                Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_ERROR
            );

            $this->getLogger()->logListingProductMessage($this->_listingProduct, $message);
            return;
        }

        parent::processCompleted($data, $params);

        $this->processSuccessRevisePrice();
        $this->processSuccessReviseQty();
        $this->processSuccessReviseVariations();
    }

    protected function processSuccessRevisePrice()
    {
        if (!$this->getConfigurator()->isPriceAllowed()) {
            return;
        }

        $currency = Mage::app()->getLocale()->currency(
            $this->_listingProduct->getMarketplace()->getChildObject()->getCurrency()
        );

        $from = $this->_listingProduct->getOrigData('online_current_price');
        $to = $this->_listingProduct->getChildObject()->getOnlineCurrentPrice();
        if ($from == $to) {
            return;
        }

        $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
        $message->initFromPreparedData(
            sprintf(
                'Price was revised from %s to %s',
                $currency->toCurrency($from),
                $currency->toCurrency($to)
            ),
            Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_SUCCESS
        );

        $this->getLogger()->logListingProductMessage($this->_listingProduct, $message);
    }

    protected function processSuccessReviseQty()
    {
        if ($this->getRequestDataObject()->isVariationItem()) {
            if (!$this->getConfigurator()->isVariationsAllowed()) {
                return;
            }
        } elseif (!$this->getConfigurator()->isQtyAllowed()) {
            return;
        }

        $from = $this->_listingProduct->getOrigData('online_qty') -
                $this->_listingProduct->getOrigData('online_qty_sold');

        $to = $this->_listingProduct->getChildObject()->getOnlineQty() -
              $this->_listingProduct->getChildObject()->getOnlineQtySold();

        if ($from == $to) {
            return;
        }

        $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
        $message->initFromPreparedData(
            sprintf('QTY was revised from %s to %s', $from, $to),
            Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_SUCCESS
        );

        $this->getLogger()->logListingProductMessage($this->_listingProduct, $message);
    }

    protected function processSuccessReviseVariations()
    {
        if (!$this->getRequestDataObject()->isVariationItem() ||
            !$this->getConfigurator()->isVariationsAllowed()
        ) {
            return;
        }

        $currency = Mage::app()->getLocale()->currency(
            $this->_listingProduct->getMarketplace()->getChildObject()->getCurrency()
        );

        $requestMetadata = $this->getResponseObject()->getRequestMetaData();
        $variationMetadata = !empty($requestMetadata['variation_data']) ? $requestMetadata['variation_data'] : array();

        foreach ($this->_listingProduct->getVariations(true) as $variation) {
            if (!isset($variationMetadata[$variation->getId()]['online_qty']) ||
                !isset($variationMetadata[$variation->getId()]['online_price'])
            ) {
                continue;
            }

            $sku = $variation->getChildObject()->getOnlineSku();
            $origPrice = $variationMetadata[$variation->getId()]['online_price'];
            $currentPrice = $variation->getChildObject()->getOnlinePrice();

            if ($currentPrice != $origPrice) {
                $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
                $message->initFromPreparedData(
                    sprintf(
                        'SKU %s: Price was revised from %s to %s',
                        $sku,
                        $currency->toCurrency($origPrice),
                        $currency->toCurrency($currentPrice)
                    ),
                    Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_SUCCESS
                );

                $this->getLogger()->logListingProductMessage($this->_listingProduct, $message);
            }

            $origQty = $variationMetadata[$variation->getId()]['online_qty'];
            $currentQty = $variation->getChildObject()->getOnlineQty();

            if ($currentQty != $origQty) {
                $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
                $message->initFromPreparedData(
                    sprintf(
                        'SKU %s: QTY was revised from %s to %s',
                        $sku,
                        $origQty,
                        $currentQty
                    ),
                    Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_SUCCESS
                );

                $this->getLogger()->logListingProductMessage($this->_listingProduct, $message);
            }
        }
    }

    //########################################

    public function eventAfterExecuting()
    {
        $responseMessages = $this->getResponse()->getMessages()->getEntities();

        if ($this->getStatusChanger() == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_SYNCH &&
            (!$this->getConfigurator()->isExcludingMode()) &&
            $this->isNewRequiredSpecificNeeded($responseMessages)) {
            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromPreparedData(
                Mage::helper('M2ePro')->__(
                    'eBay Category assigned to this Item requires the Product Identifier to be specified
                    (UPC, EAN, ISBN, etc.). The related data will be automatically submitted to the Channel based
                    on eBay Catalog Identifiers settings in the Description Policy.'
                ),
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

        parent::eventAfterExecuting();
    }

    //########################################
}