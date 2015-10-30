<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Listing_Log extends Ess_M2ePro_Model_Listing_Log
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->setComponentMode(Ess_M2ePro_Helper_Component_Buy::NICK);
    }

    //########################################

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
            $listingProduct = Mage::helper('M2ePro/Component_Buy')->getObject('Listing_Product',$listingProductId);

            /** @var Ess_M2ePro_Model_Buy_Listing_Product_Variation_Manager $variationManager */
            $variationManager = $listingProduct->getChildObject()->getVariationManager();

            if ($variationManager->isVariationProduct() && $variationManager->isVariationProductMatched()) {
                $logAdditionalData['variation_options'] = $variationManager->getProductOptions();
                $dataForAdd['additional_data'] = json_encode($logAdditionalData);
            }
        }

        $this->createMessage($dataForAdd);
    }

    //########################################
}