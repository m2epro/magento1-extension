<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2EPro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_GeneralIdOwner
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Abstract
{
    //########################################

    protected function check()
    {
        if (!$this->getProcessor()->isGeneralIdSet() || !$this->getProcessor()->isGeneralIdOwner()) {
            $this->getProcessor()->getListingProduct()->setData('sku', null);
        }
    }

    protected function execute()
    {
        $isGeneralIdOwner = $this->getProcessor()->getAmazonListingProduct()->isGeneralIdOwner();

        foreach ($this->getProcessor()->getTypeModel()->getChildListingsProducts() as $listingProduct) {

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();

            $needSave = false;

            if ($amazonListingProduct->isGeneralIdOwner() != $isGeneralIdOwner) {
                $listingProduct->setData('is_general_id_owner', $isGeneralIdOwner);
                $needSave = true;
            }

            $needSave && $listingProduct->save();
        }
    }

    //########################################
}