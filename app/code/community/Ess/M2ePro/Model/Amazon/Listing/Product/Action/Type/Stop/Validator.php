<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Stop_Validator
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Validator
{
    // ########################################

    public function validate()
    {
        $params = $this->getParams();

        if (empty($params['remove']) && !$this->validateBlocked()) {
            return false;
        }

        if ($this->getVariationManager()->isRelationParentType() && !$this->validateParentListingProductFlags()) {
            return false;
        }

        if (!$this->validatePhysicalUnitAndSimple()) {
            return false;
        }

        if ($this->getAmazonListingProduct()->isAfnChannel()) {

            // M2ePro_TRANSLATIONS
            // Stop Action for FBA Items is impossible as their Quantity is unknown. You can run Revise Action for such Items, but the Quantity value will be ignored.
            $this->addMessage('Stop Action for FBA Items is impossible as their Quantity is unknown. You can run
            Revise Action for such Items, but the Quantity value will be ignored.');

            return false;
        }

        if (!$this->getListingProduct()->isListed() || !$this->getListingProduct()->isStoppable()) {

            if (empty($params['remove'])) {

                // M2ePro_TRANSLATIONS
                // Item is not Listed or not available
                $this->addMessage('Item is not active or not available');

            } else {
                if ($this->getVariationManager()->isRelationChildType() &&
                    $this->getVariationManager()->getTypeModel()->isVariationProductMatched()
                ) {
                    $parentAmazonListingProduct = $this->getVariationManager()
                        ->getTypeModel()
                        ->getAmazonParentListingProduct();

                    $parentAmazonListingProduct->getVariationManager()->getTypeModel()->addRemovedProductOptions(
                        $this->getVariationManager()->getTypeModel()->getProductOptions()
                    );
                }

                $this->getListingProduct()->deleteInstance();
                $this->getListingProduct()->isDeleted(true);
            }

            return false;
        }

        if (!$this->validateSku()) {
            return false;
        }

        return true;
    }

    // ########################################
}