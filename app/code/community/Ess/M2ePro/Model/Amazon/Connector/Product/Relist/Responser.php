<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Relist_Response getResponseObject()
 */
class Ess_M2ePro_Model_Amazon_Connector_Product_Relist_Responser
    extends Ess_M2ePro_Model_Amazon_Connector_Product_Responser
{
    //########################################

    protected function getSuccessfulMessage()
    {
        $currency = Mage::app()->getLocale()->currency(
            $this->_listingProduct->getMarketplace()->getChildObject()->getCurrency()
        );

        $parts = array(
            sprintf('Product was Relisted with QTY %d', $this->_listingProduct->getChildObject()->getOnlineQty())
        );

        if ($regularPrice = $this->_listingProduct->getChildObject()->getOnlineRegularPrice()) {
            $parts[] = sprintf('Regular Price %s', $currency->toCurrency($regularPrice));
        }

        if ($businessPrice = $this->_listingProduct->getChildObject()->getOnlineBusinessPrice()) {
            $parts[] = sprintf('Business Price %s', $currency->toCurrency($businessPrice));
        }

        return implode(', ', $parts);
    }

    //########################################
}
