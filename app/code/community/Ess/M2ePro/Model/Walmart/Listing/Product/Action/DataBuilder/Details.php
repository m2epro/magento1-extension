<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Action_DataBuilder_Details
    extends Ess_M2ePro_Model_Walmart_Listing_Product_Action_DataBuilder_Abstract
{
    //########################################

    public function getData()
    {
        $sellingFormatTemplateSource = $this->getWalmartListingProduct()->getSellingFormatTemplateSource();

        $data = array(
            'product_data_nick'     => $this->getWalmartListingProduct()->getCategoryTemplate()->getProductDataNick(),
            'product_data'          => $this->getProductData(),
            'product_ids_data'      => $this->getProductIdsData(),
            'description_data'      => $this->getDescriptionData(),
            'shipping_weight'       => $sellingFormatTemplateSource->getItemWeight(),
            'tax_code'              => $sellingFormatTemplateSource->getProductTaxCode(),
            'additional_attributes' => $sellingFormatTemplateSource->getAttributes(),
        );

        if ($this->getWalmartListingProduct()->getWpid()) {
            $data['wpid'] = $this->getWalmartListingProduct()->getWpid();
        }

        $startDate = $this->getWalmartListingProduct()->getSellingFormatTemplateSource()->getStartDate();
        if (!empty($startDate)) {
            $data['start_date'] = $startDate;
        } else {
            $data['start_date'] = Mage::helper('M2ePro')->getCurrentGmtDate(false, 'Y-m-d');
        }

        $endDate = $this->getWalmartListingProduct()->getSellingFormatTemplateSource()->getEndDate();
        if (!empty($endDate)) {
            $data['end_date'] = $endDate;
        } else {
            $data['end_date'] = '9999-01-01';
        }

        $mapPrice = $this->getWalmartListingProduct()->getMapPrice();
        if (!empty($mapPrice)) {
            $data['map_price'] = $mapPrice;
        }

        $mustShipAlone = $this->getWalmartListingProduct()->getSellingFormatTemplateSource()->getMustShipAlone();
        if (!is_null($mustShipAlone)) {
            $data['is_must_ship_alone'] = $mustShipAlone;
        }

        $shipsInOriginalPackaging = $sellingFormatTemplateSource->getShipsInOriginalPackaging();
        if (!is_null($shipsInOriginalPackaging)) {
            $data['is_ship_in_original_packaging'] = $shipsInOriginalPackaging;
        }

        $shippingOverrides = $this->getShippingOverrides();
        if (!empty($shippingOverrides)) {
            $data['shipping_overrides'] = $shippingOverrides;
        }

        if ($this->getWalmartListingProduct()->getVariationManager()->isRelationChildType()) {
            /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Child $typeModel */
            $typeModel = $this->getWalmartListingProduct()->getVariationManager()->getTypeModel();

            /** @var Ess_M2ePro_Model_Listing_Product $parentListingProduct */
            $parentListingProduct = $typeModel->getParentListingProduct();

            /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent $parentTypeModel */
            $parentTypeModel = $parentListingProduct->getChildObject()->getVariationManager()->getTypeModel();

            if ($parentTypeModel->hasChannelGroupId()) {
                $variationGroupId = $parentTypeModel->getChannelGroupId();
            } else {
                $variationGroupId = Mage::helper('M2ePro')->generateUniqueHash($parentListingProduct->getId());
                $parentTypeModel->setChannelGroupId($variationGroupId, true);
            }

            $data['variation_data'] = array(
                'group_id'   => $variationGroupId,
                'attributes' => $typeModel->getRealChannelOptions(),
            );
        }

        return $data;
    }

    //########################################

    private function getProductData()
    {
        $data = array();

        $this->searchNotFoundAttributes();

        foreach ($this->getWalmartListingProduct()->getCategoryTemplate()->getSpecifics(true) as $specific) {

            $source = $specific->getSource($this->getWalmartListingProduct()->getActualMagentoProduct());

            if (!$specific->isRequired() && !$specific->isModeNone() && !$source->getValue()) {
                continue;
            }

            $data = Mage::helper('M2ePro')->arrayReplaceRecursive(
                $data, Mage::helper('M2ePro')->jsonDecode($source->getPath())
            );
        }

        $this->processNotFoundAttributes('Product Specifics');

        return $data;
    }

    // ---------------------------------------

    private function getProductIdsData()
    {
        $data = array();

        $idsTypes = array('gtin', 'upc', 'ean', 'isbn');

        foreach ($idsTypes as $idType) {
            if (!isset($this->cachedData[$idType])) {
                continue;
            }

            $data[] = array(
                'type' => strtoupper($idType),
                'id'   => $this->cachedData[$idType]
            );
        }

        return $data;
    }

    // ---------------------------------------

    private function getDescriptionData()
    {
        $source = $this->getWalmartListingProduct()->getDescriptionTemplateSource();

        $data = array();

        $this->searchNotFoundAttributes();
        $data['title'] = $source->getTitle();
        $this->processNotFoundAttributes('Title');

        $this->searchNotFoundAttributes();
        $data['brand'] = $source->getBrand();
        $this->processNotFoundAttributes('Brand');

        $this->searchNotFoundAttributes();
        $data['manufacturer'] = $source->getManufacturer();
        $this->processNotFoundAttributes('Manufacturer');

        $this->searchNotFoundAttributes();
        $data['manufacturer_part_number'] = $source->getManufacturerPartNumber();
        $this->processNotFoundAttributes('Manufacturer Part Number');

        $this->searchNotFoundAttributes();
        $data['count_per_pack'] = $source->getCountPerPack();
        $this->processNotFoundAttributes('Count Per Pack');

        $this->searchNotFoundAttributes();
        $data['multipack_quantity'] = $source->getMultipackQuantity();
        $this->processNotFoundAttributes('Multipack Quantity');

        $this->searchNotFoundAttributes();
        $data['count'] = $source->getTotalCount();
        $this->processNotFoundAttributes('Total Count');

        $this->searchNotFoundAttributes();
        $data['model_number'] = $source->getModelNumber();
        $this->processNotFoundAttributes('Model Number');

        $this->searchNotFoundAttributes();
        $data['short_description'] = $source->getDescription();
        $this->processNotFoundAttributes('Short Description');

        $this->searchNotFoundAttributes();
        $data['key_features'] = $source->getKeyFeatures();
        $this->processNotFoundAttributes('Key Features');

        $this->searchNotFoundAttributes();
        $data['features'] = $source->getOtherFeatures();
        $this->processNotFoundAttributes('Other Features');

        $this->searchNotFoundAttributes();
        $data['keywords'] = $source->getKeywords();
        $this->processNotFoundAttributes('Keywords');

        $this->searchNotFoundAttributes();
        $data['msrp'] = $source->getMsrpRrp();
        $this->processNotFoundAttributes('MSRP / RRP');

        $this->searchNotFoundAttributes();
        $data['main_image_url'] = $this->getMainImageUrl();
        $this->processNotFoundAttributes('Other Features');

        $this->searchNotFoundAttributes();
        $data['product_secondary_image_url'] = $this->getProductSecondaryImageUrls();
        $this->processNotFoundAttributes('Gallery Images');

        if ($this->getVariationManager()->isRelationChildType()) {
            $data['swatch_images'] = $this->getSwatchImages();
        }

        $this->searchNotFoundAttributes();
        $data['additional_attributes'] = $source->getAttributes();
        $this->processNotFoundAttributes('Attributes');

        return $data;
    }

    private function getMainImageUrl()
    {
        $mainImage = $this->getWalmartListingProduct()->getDescriptionTemplateSource()->getMainImage();

        if (is_null($mainImage)) {
            return '';
        }

        return $mainImage->getUrl();
    }

    private function getProductSecondaryImageUrls()
    {
        $urls = array();

        foreach ($this->getWalmartListingProduct()->getDescriptionTemplateSource()->getGalleryImages() as $image) {
            if (!$image->getUrl()) {
                continue;
            }
            $urls[] = $image->getUrl();
        }

        return $urls;
    }

    private function getSwatchImages()
    {
        if (!$this->getVariationManager()->isRelationChildType()) {
            return array();
        }

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
        $childTypeModel = $this->getVariationManager()->getTypeModel();

        $swatchAttribute = $childTypeModel->getParentTypeModel()->getSwatchImagesAttribute();
        if (empty($swatchAttribute)) {
            return array();
        }

        $image = $this->getWalmartListingProduct()->getDescriptionTemplateSource()->getVariationDifferenceImage();
        if (is_null($image)) {
            return array();
        }

        $swatchImageData = array(
            'url'          => $image->getUrl(),
            'by_attribute' => $swatchAttribute,
        );

        return array($swatchImageData);
    }

    // ---------------------------------------

    private function getShippingOverrides()
    {
        $result = array();

        $shippingOverridesServices = $this->getWalmartListingProduct()->getWalmartSellingFormatTemplate()
            ->getShippingOverrideServices(true);

        if (empty($shippingOverridesServices)) {
            return $result;
        }

        foreach ($shippingOverridesServices as $shippingOverridesService) {
            $source = $shippingOverridesService->getSource(
                $this->getWalmartListingProduct()->getActualMagentoProduct()
            );

            $result[] = array(
                'ship_method'         => $shippingOverridesService->getMethod(),
                'ship_region'         => $shippingOverridesService->getRegion(),
                'ship_price'          => $source->getCost(),
                'is_shipping_allowed' => true,
            );
        }

        return $result;
    }

    //########################################
}