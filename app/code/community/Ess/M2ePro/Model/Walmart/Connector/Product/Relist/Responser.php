<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Relist_Response getResponseObject()
 */
class Ess_M2ePro_Model_Walmart_Connector_Product_Relist_Responser
    extends Ess_M2ePro_Model_Walmart_Connector_Product_Responser
{
    //########################################

    protected function getSuccessfulMessage()
    {
        $currency = Mage::app()->getLocale()->currency(
            $this->_listingProduct->getMarketplace()->getChildObject()->getCurrency()
        );

        return sprintf(
            'Product was Relisted with QTY %d, Price %s',
            $this->_listingProduct->getChildObject()->getOnlineQty(),
            $currency->toCurrency($this->_listingProduct->getChildObject()->getOnlinePrice())
        );
    }

    //########################################
}
