<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Template
    extends Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Abstract
{
    //########################################

    protected function check() {}

    protected function execute()
    {
        $categoryTemplateId = $this->getProcessor()->getWalmartListingProduct()->getTemplateCategoryId();

        foreach ($this->getProcessor()->getTypeModel()->getChildListingsProducts() as $listingProduct) {

            /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
            $walmartListingProduct = $listingProduct->getChildObject();

            $needSave = false;

            if ($walmartListingProduct->getTemplateCategoryId() != $categoryTemplateId) {
                $listingProduct->setData('template_category_id', $categoryTemplateId);
                $needSave = true;
            }

            $needSave && $listingProduct->save();
        }
    }

    //########################################
}