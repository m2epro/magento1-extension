<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Stop_Validator
    extends Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Validator
{
    //########################################

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception
     */
    public function validate()
    {
        if (!$this->validateMagentoProductType()) {
            return false;
        }

        if (!$this->validateSku()) {
            return false;
        }

        if (!$this->validateCategory()) {
            return false;
        }

        $params = $this->getParams();
        if (empty($params['remove']) && !$this->validateMissedOnChannelBlocked()) {
            return false;
        }

        if ($this->getVariationManager()->isRelationParentType() && !$this->validateParentListingProductFlags()) {
            return false;
        }

        if (!$this->validatePhysicalUnitAndSimple()) {
            return false;
        }

        if (!$this->getListingProduct()->isListed() || !$this->getListingProduct()->isStoppable()) {
            if (empty($params['remove'])) {
                // M2ePro_TRANSLATIONS
                // Item is not Listed or not available
                $this->addMessage('Item is not active or not available');
            } else {
                $removeHandler = Mage::getModel(
                    'M2ePro/Walmart_Listing_Product_RemoveHandler',
                    array('listing_product' => $this->getListingProduct())
                );
                $removeHandler->process();
            }

            return false;
        }

        return true;
    }

    //########################################
}