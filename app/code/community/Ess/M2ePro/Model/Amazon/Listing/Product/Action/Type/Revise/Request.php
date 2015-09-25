<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Revise_Request
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Request
{
    // ########################################

    protected function getActionData()
    {
        $data = array_merge(
            array(
                'sku' => $this->getAmazonListingProduct()->getSku()
            ),
            $this->getRequestQty()->getData(),
            $this->getRequestPrice()->getData(),
            $this->getRequestDetails()->getData(),
            $this->getRequestImages()->getData(),
            $this->getRequestShippingOverride()->getData()
        );

        if ($this->getVariationManager()->isRelationChildType()) {
            $variationData = array(
                'parentage'  => Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Request::PARENTAGE_CHILD,
                'attributes' => $this->getVariationManager()->getTypeModel()->getChannelOptions(),
            );

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $parentAmazonListingProduct */
            $parentAmazonListingProduct = $this->getVariationManager()
                ->getTypeModel()
                ->getParentListingProduct()
                ->getChildObject();

            $parentSku = $parentAmazonListingProduct->getSku();
            if (!empty($parentSku)) {
                $variationData['parent_sku'] = $parentSku;
            }

            $channelTheme = $parentAmazonListingProduct->getVariationManager()->getTypeModel()->getChannelTheme();
            if (!empty($channelTheme)) {
                $variationData['theme'] = $channelTheme;
            }

            $data['variation_data'] = $variationData;
        }

        return $data;
    }

    // ########################################
}