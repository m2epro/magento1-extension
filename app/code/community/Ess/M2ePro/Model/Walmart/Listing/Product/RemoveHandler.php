<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_RemoveHandler extends Ess_M2ePro_Model_Listing_Product_RemoveHandler
{
    /** @var Ess_M2ePro_Model_Walmart_Listing_Product */
    private $parentWalmartListingProductForProcess = NULL;

    //########################################

    protected function eventBeforeProcess()
    {
        parent::eventBeforeProcess();

        $variationManager = $this->getWalmartListingProduct()->getVariationManager();

        if ($variationManager->isRelationChildType()) {

            /** @var Ess_M2ePro_Model_Walmart_Listing_Product $parentWalmartListingProduct */
            $parentWalmartListingProduct = $variationManager
                ->getTypeModel()
                ->getWalmartParentListingProduct();

            $this->parentWalmartListingProductForProcess = $parentWalmartListingProduct;

            /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
            $childTypeModel = $variationManager->getTypeModel();

            if ($childTypeModel->isVariationProductMatched()) {
                $parentWalmartListingProduct->getVariationManager()->getTypeModel()->addRemovedProductOptions(
                    $variationManager->getTypeModel()->getProductOptions()
                );
            }
        }
    }

    protected function eventAfterProcess()
    {
        parent::eventAfterProcess();

        if (is_null($this->parentWalmartListingProductForProcess)) {
            return;
        }

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent $parentTypeModel */
        $parentTypeModel = $this->parentWalmartListingProductForProcess->getVariationManager()->getTypeModel();
        $parentTypeModel->getProcessor()->process();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Walmart_Listing_Product
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    private function getWalmartListingProduct()
    {
        return $this->listingProduct->getChildObject();
    }

    //########################################
}