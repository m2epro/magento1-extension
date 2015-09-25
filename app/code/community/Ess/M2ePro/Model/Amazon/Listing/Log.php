<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Log extends Ess_M2ePro_Model_Listing_Log
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);
    }

    // ########################################

    public function addProductMessage($listingId,
                                      $productId,
                                      $listingProductId,
                                      $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN,
                                      $actionId = NULL,
                                      $action = NULL,
                                      $description = NULL,
                                      $type = NULL,
                                      $priority = NULL)
    {
        $dataForAdd = $this->makeDataForAdd($listingId,
                                            $initiator,
                                            $productId,
                                            $listingProductId,
                                            $actionId,
                                            $action,
                                            $description,
                                            $type,
                                            $priority);

        if (!empty($listingProductId)) {

            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $listingProductId);

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager $variationManager */
            $variationManager = $listingProduct->getChildObject()->getVariationManager();

            if ($variationManager->isPhysicalUnit() &&
                $variationManager->getTypeModel()->isVariationProductMatched()
            ) {
                $productOptions = $variationManager->getTypeModel()->getProductOptions();

                if (!empty($productOptions)) {
                    $logAdditionalData['variation_options'] = $productOptions;
                    $dataForAdd['additional_data'] = json_encode($logAdditionalData);
                }
            }

            if ($variationManager->isRelationChildType()) {
                $dataForAdd['parent_listing_product_id'] = $variationManager->getVariationParentId();
            }
        }

        $this->createMessage($dataForAdd);
    }

    // ########################################
}