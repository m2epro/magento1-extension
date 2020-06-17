<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Log extends Ess_M2ePro_Model_Listing_Log
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->setComponentMode(Ess_M2ePro_Helper_Component_Walmart::NICK);
    }

    //########################################

    /**
     * @param $listingId
     * @param $productId
     * @param $listingProductId
     * @param int $initiator
     * @param null $actionId
     * @param null $action
     * @param null $description
     * @param null $type
     * @param array $additionalData
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function addProductMessage(
        $listingId,
        $productId,
        $listingProductId,
        $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN,
        $actionId = null,
        $action = null,
        $description = null,
        $type = null,
        array $additionalData = array()
    ) {
        $dataForAdd = $this->makeDataForAdd(
            $listingId,
            $initiator,
            $productId,
            $listingProductId,
            $actionId,
            $action,
            $description,
            $type,
            $additionalData
        );

        if (!empty($listingProductId)) {

            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $listingProduct = Mage::helper('M2ePro/Component_Walmart')->getObject('Listing_Product', $listingProductId);

            /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager $variationManager */
            $variationManager = $listingProduct->getChildObject()->getVariationManager();

            if ($variationManager->isPhysicalUnit() &&
                $variationManager->getTypeModel()->isVariationProductMatched()
            ) {
                $productOptions = $variationManager->getTypeModel()->getProductOptions();

                if (!empty($productOptions)) {
                    $dataForAdd['additional_data'] = (array)Mage::helper('M2ePro')->jsonDecode(
                        $dataForAdd['additional_data']
                    );
                    $dataForAdd['additional_data']['variation_options'] = $productOptions;
                    $dataForAdd['additional_data'] = Mage::helper('M2ePro')->jsonEncode(
                        $dataForAdd['additional_data']
                    );
                }
            }

            if ($variationManager->isRelationChildType()) {
                $dataForAdd['parent_listing_product_id'] = $variationManager->getVariationParentId();
            }
        }

        $this->createMessage($dataForAdd);
    }

    //########################################
}
