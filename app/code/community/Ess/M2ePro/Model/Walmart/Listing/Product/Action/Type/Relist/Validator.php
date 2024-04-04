<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Relist_Validator
    extends Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Validator
{
    //########################################

    /**
     * @return bool
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

        if (!$this->validateMissedOnChannelBlocked()) {
            return false;
        }

        if (!$this->validateGeneralBlocked()) {
            return false;
        }

        if ($this->getVariationManager()->isRelationParentType() && !$this->validateParentListingProduct()) {
            return false;
        }

        if (!$this->validatePhysicalUnitAndSimple()) {
            return false;
        }

        if ($this->getVariationManager()->isPhysicalUnit() && !$this->validatePhysicalUnitMatching()) {
            return false;
        }

        if (!$this->getListingProduct()->isInactive() &&
            (!$this->getListingProduct()->isBlocked() || !$this->getWalmartListingProduct()->isOnlinePriceInvalid())) {
            $this->addMessage(
                'The Item either is Listed, or not Listed yet or not available'
            );

            return false;
        }

        if (!$this->validateQty()) {
            return false;
        }

        if (!$this->validatePrice()) {
            return false;
        }

        if (!$this->validatePromotions()) {
            return false;
        }

        if (!$this->validatePriceAndPromotionsFeedBlocked()) {
            return false;
        }

        return true;
    }

    //########################################
}