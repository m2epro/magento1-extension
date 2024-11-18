<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Listing_Product_Action_DataBuilder_Qty as QtyBuilder;

/**
 * @method Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Revise_Response getResponseObject()
 */
class Ess_M2ePro_Model_Amazon_Connector_Product_Revise_Responser
    extends Ess_M2ePro_Model_Amazon_Connector_Product_Responser
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

        if ($this->getConfigurator()->isQtyAllowed()) {
            $params = $this->getResponseObject()->getParams();

            if (!empty($params['switch_to']) && $params['switch_to'] === QtyBuilder::FULFILLMENT_MODE_AFN) {
                return 'Item was switched to AFN';
            }

            if (!empty($params['switch_to']) && $params['switch_to'] === QtyBuilder::FULFILLMENT_MODE_MFN) {
                return 'Item was switched to MFN';
            }
        }

        if ($this->getConfigurator()->isDetailsAllowed()) {
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

        $this->processSuccessReviseRegularPrice();
        $this->processSuccessReviseBusinessPrice();
        $this->processSuccessReviseQty();
    }

    protected function processSuccessReviseRegularPrice()
    {
        if (!$this->getConfigurator()->isRegularPriceAllowed()) {
            return;
        }

        $currency = Mage::app()->getLocale()->currency(
            $this->_listingProduct->getMarketplace()->getChildObject()->getCurrency()
        );

        $from = $this->_listingProduct->getOrigData('online_regular_price');
        $to = $this->_listingProduct->getChildObject()->getOnlineRegularPrice();
        
        if ($from != $to) {
            $this->logSuccessMessage(
                sprintf(
                    'Regular Price was revised from %s to %s',
                    $currency->toCurrency($from),
                    $currency->toCurrency($to)
                )
            );
        }
    }

    protected function processSuccessReviseBusinessPrice()
    {
        if (!$this->getConfigurator()->isBusinessPriceAllowed()) {
            return;
        }

        $currency = Mage::app()->getLocale()->currency(
            $this->_listingProduct->getMarketplace()->getChildObject()->getCurrency()
        );

        $from = $this->_listingProduct->getOrigData('online_business_price');
        $to = $this->_listingProduct->getChildObject()->getOnlineBusinessPrice();
        
        if ($from != $to) {
            $this->logSuccessMessage(
                sprintf(
                    'Business Price was revised from %s to %s',
                    $currency->toCurrency($from),
                    $currency->toCurrency($to)
                )
            );
        }
    }

    protected function processSuccessReviseQty()
    {
        if (!$this->getConfigurator()->isQtyAllowed()) {
            return;
        }

        $handlingTimeFrom = $this->_listingProduct->getOrigData('online_handling_time');
        $handlingTimeTo = $this->_listingProduct->getChildObject()->getOnlineHandlingTime();
        
        if ($handlingTimeFrom != $handlingTimeTo) {
            $this->logSuccessMessage(
                sprintf('Handling Time was revised from %s to %s', $handlingTimeFrom, $handlingTimeTo)
            );
        }

        $qtyFrom = $this->_listingProduct->getOrigData('online_qty');
        $qtyTo = $this->_listingProduct->getChildObject()->getOnlineQty();
        
        if ($qtyFrom != $qtyTo) {
            $this->logSuccessMessage(
                sprintf('QTY was revised from %s to %s', $qtyFrom, $qtyTo)
            );
        }
    }

    protected function logSuccessMessage($text)
    {
        /** @var Ess_M2ePro_Model_Connector_Connection_Response_Message $message */
        $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
        $message->initFromPreparedData($text, Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_SUCCESS);
        $this->getLogger()->logListingProductMessage($this->_listingProduct, $message);
    }

    //########################################
}
