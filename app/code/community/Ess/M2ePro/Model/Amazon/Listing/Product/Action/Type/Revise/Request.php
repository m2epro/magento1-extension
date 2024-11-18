<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess\M2ePro\Model\Amazon\Listing\Product\Action\Type\ListAction\Request as ListActionRequest;

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Revise_Request
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Request
{
    //########################################

    protected function getActionData()
    {
        $data = array_merge(
            array(
                'sku' => $this->getAmazonListingProduct()->getSku()
            ),
            $this->getProductIdentifierData(),
            $this->getQtyData(),
            $this->getRegularPriceData(),
            $this->getBusinessPriceData(),
            $this->getDetailsData()
        );

        if ($this->getVariationManager()->isRelationParentType()) {
            $channelTheme = $this->getVariationManager()->getTypeModel()->getChannelTheme();

            $data['variation_data'] = array(
                'parentage' => Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Request::PARENTAGE_PARENT,
                'theme' => $channelTheme,
            );
        } elseif ($this->getVariationManager()->isRelationChildType()) {
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

    private function getProductIdentifierData()
    {
        $productType = $this->getAmazonListingProduct()->getProductTypeTemplate();
        if (
            $productType === null
            || $productType->getNick() === Ess_M2ePro_Model_Amazon_Template_ProductType::GENERAL_PRODUCT_TYPE_NICK
        ) {
            return array();
        }

        $productIdentifiers = $this->getAmazonListingProduct()->getIdentifiers();
        $data = array();

        if ($worldwideId = $productIdentifiers->getWorldwideId()) {
            $data['product_id'] = $worldwideId->getIdentifier();
            $data['product_id_type'] = $worldwideId->isUPC() ? 'UPC' : 'EAN';
        }

        return $data;
    }

    //########################################
}
