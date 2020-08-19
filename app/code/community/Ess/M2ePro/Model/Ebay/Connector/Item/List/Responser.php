<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Connector_Item_List_Responser
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
                'Product was Listed with QTY %d, Price %s - %s',
                $onlineQty,
                $currency->toCurrency($this->getRequestDataObject()->getVariationMinPrice($calculateWithEmptyQty)),
                $currency->toCurrency($this->getRequestDataObject()->getVariationMaxPrice($calculateWithEmptyQty))
            );
        }

        return sprintf(
            'Product was Listed with QTY %d, Price %s',
            $onlineQty,
            $currency->toCurrency($this->_listingProduct->getChildObject()->getOnlineCurrentPrice())
        );
    }

    //########################################

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