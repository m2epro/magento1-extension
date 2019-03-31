<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Connector_Item_Revise_Requester
    extends Ess_M2ePro_Model_Ebay_Connector_Item_Requester
{
    //########################################

    protected function getCommand()
    {
        return array('item','update','revise');
    }

    protected function getActionType()
    {
        return Ess_M2ePro_Model_Listing_Product::ACTION_REVISE;
    }

    protected function getLogsAction()
    {
        return Ess_M2ePro_Model_Listing_Log::ACTION_REVISE_PRODUCT_ON_COMPONENT;
    }

    //########################################

    public function process()
    {
        $this->initOutOfStockControlLogic();
        parent::process();
    }

    //########################################

    protected function initOutOfStockControlLogic()
    {
        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $this->listingProduct->getChildObject();

        $outOfStockControlCurrentState = $ebayListingProduct->getOutOfStockControl();
        $outOfStockControlTemplateState = $ebayListingProduct->getEbaySellingFormatTemplate()
                                                             ->getOutOfStockControl();

        if (!$outOfStockControlCurrentState && $outOfStockControlTemplateState) {
            $outOfStockControlCurrentState = true;
        }

        $this->params['out_of_stock_control_current_state'] = $outOfStockControlCurrentState;
        $this->params['out_of_stock_control_result'] = $outOfStockControlCurrentState
                                                       || $ebayListingProduct->getEbayAccount()
                                                                             ->getOutOfStockControl();
    }

    // ########################################
}