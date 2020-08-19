<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Revise_Request as ReviseRequest;

/**
 * @method Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Revise_Response getResponseObject()
 */
class Ess_M2ePro_Model_Walmart_Connector_Product_Revise_Responser
    extends Ess_M2ePro_Model_Walmart_Connector_Product_Responser
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

        if ($this->getConfigurator()->isPromotionsAllowed()) {
            $sequenceStrings[] = 'Promotions';
        }

        if ($this->getConfigurator()->isDetailsAllowed()) {
            if ($this->getRequestDataObject()->getIsNeedSkuUpdate()) {
                $sequenceStrings[] = 'SKU';
            }

            if ($this->getRequestDataObject()->getIsNeedProductIdUpdate()) {
                $ids = $this->getResponseObject()->getRequestMetaData(ReviseRequest::PRODUCT_ID_UPDATE_METADATA_KEY);
                !empty($ids) && $sequenceStrings[] = strtoupper($ids['type']);
            }

            $sequenceStrings[] = 'Details';
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

    protected function processSuccess(array $params = array())
    {
        parent::processSuccess($params);

        $this->processSuccessRevisePrice();
        $this->processSuccessReviseQty();
    }

    protected function processSuccessRevisePrice()
    {
        if (!$this->getConfigurator()->isPriceAllowed()) {
            return;
        }

        $currency = Mage::app()->getLocale()->currency(
            $this->_listingProduct->getMarketplace()->getChildObject()->getCurrency()
        );

        $from = $this->_listingProduct->getOrigData('online_price');
        $to = $this->_listingProduct->getChildObject()->getOnlinePrice();
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
        if (!$this->getConfigurator()->isQtyAllowed()) {
            return;
        }

        $from = $this->_listingProduct->getOrigData('online_qty');
        $to = $this->_listingProduct->getChildObject()->getOnlineQty();
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

    //########################################
}
