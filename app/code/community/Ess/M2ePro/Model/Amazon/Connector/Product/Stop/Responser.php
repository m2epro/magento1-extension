<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Connector_Product_Stop_Responser
    extends Ess_M2ePro_Model_Amazon_Connector_Product_Responser
{
    /** @var Ess_M2ePro_Model_Listing_Product $_parentForProcessing */
    protected $_parentForProcessing = null;

    // ########################################

    protected function getSuccessfulMessage()
    {
        // M2ePro_TRANSLATIONS
        // Item was successfully Stopped
        return 'Item was successfully Stopped';
    }

    // ########################################

    public function eventAfterExecuting()
    {
        if (!empty($this->_params['params']['remove'])) {
            $removeHandler = Mage::getModel(
                'M2ePro/Amazon_Listing_Product_RemoveHandler', array('listing_product' => $this->_listingProduct)
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

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $this->_parentForProcessing->getChildObject();
        $amazonListingProduct->getVariationManager()->getTypeModel()->getProcessor()->process();
    }

    // ########################################
}
