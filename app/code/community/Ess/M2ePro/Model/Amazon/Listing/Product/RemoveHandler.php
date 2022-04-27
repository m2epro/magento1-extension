<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_RemoveHandler extends Ess_M2ePro_Model_Listing_Product_RemoveHandler
{
    /** @var Ess_M2ePro_Model_Amazon_Listing_Product */
    protected $_parentAmazonListingProductForProcess = null;

    //########################################

    protected function eventBeforeProcess()
    {
        parent::eventBeforeProcess();

        $variationManager = $this->getAmazonListingProduct()->getVariationManager();

        if ($variationManager->isRelationChildType()) {

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $parentAmazonListingProduct */
            $parentAmazonListingProduct = $variationManager
                ->getTypeModel()
                ->getAmazonParentListingProduct();

            $this->_parentAmazonListingProductForProcess = $parentAmazonListingProduct;

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
            $childTypeModel = $variationManager->getTypeModel();

            if ($childTypeModel->isVariationProductMatched()) {
                $parentAmazonListingProduct->getVariationManager()->getTypeModel()->addRemovedProductOptions(
                    $variationManager->getTypeModel()->getProductOptions()
                );
            }
        }
    }

    protected function eventAfterProcess()
    {
        parent::eventAfterProcess();

        if ($this->_parentAmazonListingProductForProcess === null) {
            return;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent $parentTypeModel */
        $parentTypeModel = $this->_parentAmazonListingProductForProcess->getVariationManager()->getTypeModel();
        try {
            $parentTypeModel->getProcessor()->process();
        } catch (\Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
        }
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getAmazonListingProduct()
    {
        return $this->_listingProduct->getChildObject();
    }

    //########################################
}
