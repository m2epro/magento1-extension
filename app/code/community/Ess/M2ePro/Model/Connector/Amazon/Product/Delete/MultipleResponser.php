<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Amazon_Product_Delete_MultipleResponser
    extends Ess_M2ePro_Model_Connector_Amazon_Product_Responser
{
    /** @var Ess_M2ePro_Model_Listing_Product[] $parentsForProcessing */
    protected $parentsForProcessing = array();

    // ########################################

    protected function getSuccessfulMessage(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        // M2ePro_TRANSLATIONS
        // Item was successfully Deleted
        return 'Item was successfully Deleted';
    }

    // ########################################

    public function eventAfterExecuting()
    {
        if (!empty($this->params['params']['remove'])) {
            foreach ($this->listingsProducts as $listingProduct) {
                /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
                $amazonListingProduct = $listingProduct->getChildObject();

                $variationManager = $amazonListingProduct->getVariationManager();

                if ($variationManager->isRelationChildType()) {
                    $childTypeModel = $variationManager->getTypeModel();

                    $parentListingProduct = $childTypeModel->getParentListingProduct();
                    $this->parentsForProcessing[$parentListingProduct->getId()] = $parentListingProduct;

                    if ($childTypeModel->isVariationProductMatched()) {
                        $parentAmazonListingProduct = $childTypeModel->getAmazonParentListingProduct();

                        $parentAmazonListingProduct->getVariationManager()->getTypeModel()->addRemovedProductOptions(
                            $childTypeModel->getProductOptions()
                        );
                    }
                }

                $listingProduct->setData('status', Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED);
                $listingProduct->save();
                $listingProduct->deleteInstance();
            }
        }

        parent::eventAfterExecuting();
    }

    protected function inspectProducts()
    {
        if (empty($this->params['params']['remove'])) {
            parent::inspectProducts();
        }
    }

    protected function processParentProcessors()
    {
        if (empty($this->params['params']['remove'])) {
            parent::processParentProcessors();
            return;
        }

        foreach ($this->parentsForProcessing as $listingProduct) {
            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();
            $amazonListingProduct->getVariationManager()->getTypeModel()->getProcessor()->process();
        }
    }

    // ########################################
}