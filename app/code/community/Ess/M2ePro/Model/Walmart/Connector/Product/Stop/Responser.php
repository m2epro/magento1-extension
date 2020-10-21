<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Connector_Product_Stop_Responser
    extends Ess_M2ePro_Model_Walmart_Connector_Product_Responser
{
    /** @var Ess_M2ePro_Model_Listing_Product $_parentForProcessing */
    protected $_parentForProcessing = null;

    //########################################

    protected function getSuccessfulMessage()
    {
        return 'Item was Stopped';
    }

    //########################################

    public function eventAfterExecuting()
    {
        if (!empty($this->_params['params']['remove'])) {
            $removeHandler = Mage::getModel(
                'M2ePro/Walmart_Listing_Product_RemoveHandler', array('listing_product' => $this->_listingProduct)
            );
            $removeHandler->process();
        }

        parent::eventAfterExecuting();
    }

    protected function processParentProcessor()
    {
        if (empty($this->_params['params']['remove'])) {
            parent::processParentProcessor();
            return;
        }

        if ($this->_parentForProcessing === null) {
            return;
        }

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
        $walmartListingProduct = $this->_parentForProcessing->getChildObject();
        $walmartListingProduct->getVariationManager()->getTypeModel()->getProcessor()->process();
    }

    //########################################
}
