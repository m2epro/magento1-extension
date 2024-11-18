<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Template
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Abstract
{
    //########################################

    protected function check()
    {
        return null;
    }

    protected function execute()
    {
        $productTypeTemplateId    = $this->getProcessor()->getAmazonListingProduct()->getTemplateProductTypeId();
        $shippingTemplateId       = $this->getProcessor()->getAmazonListingProduct()->getTemplateShippingId();
        $productTaxCodeTemplateId = $this->getProcessor()->getAmazonListingProduct()->getTemplateProductTaxCodeId();

        foreach ($this->getProcessor()->getTypeModel()->getChildListingsProducts() as $listingProduct) {

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();

            $needSave = false;

            if ($amazonListingProduct->getTemplateProductTypeId() != $productTypeTemplateId) {
                $listingProduct->setData('template_product_type_id', $productTypeTemplateId);
                $needSave = true;
            }

            if ($amazonListingProduct->getTemplateShippingId() != $shippingTemplateId) {
                $listingProduct->setData('template_shipping_id', $shippingTemplateId);
                $needSave = true;
            }

            if ($amazonListingProduct->getTemplateProductTaxCodeId() != $productTaxCodeTemplateId) {
                $listingProduct->setData('template_product_tax_code_id', $productTaxCodeTemplateId);
                $needSave = true;
            }

            $needSave && $listingProduct->save();
        }
    }

    //########################################
}
