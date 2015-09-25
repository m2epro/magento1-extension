<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2EPro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Template
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Abstract
{
    // ##########################################################

    protected function check() {}

    protected function execute()
    {
        $descriptionTemplateId = $this->getProcessor()->getAmazonListingProduct()->getTemplateDescriptionId();
        $shippingOverrideTemplateId = $this->getProcessor()->getAmazonListingProduct()->getTemplateShippingOverrideId();

        foreach ($this->getProcessor()->getTypeModel()->getChildListingsProducts() as $listingProduct) {

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();

            $needSave = false;

            if ($amazonListingProduct->getTemplateDescriptionId() != $descriptionTemplateId) {
                $listingProduct->setData('template_description_id', $descriptionTemplateId);
                $needSave = true;
            }

            if ($amazonListingProduct->getTemplateShippingOverrideId() != $shippingOverrideTemplateId) {
                $listingProduct->setData('template_shipping_override_id', $shippingOverrideTemplateId);
                $needSave = true;
            }

            $needSave && $listingProduct->save();
        }
    }

    // ##########################################################
}