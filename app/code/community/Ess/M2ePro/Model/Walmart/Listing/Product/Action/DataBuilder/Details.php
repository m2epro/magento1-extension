<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Helper_Component_Walmart_Configuration as ConfigurationHelper;

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
            'product_id_data'      => $this->getProductIdData(),
            'description_data'      => $this->getDescriptionData(),
            'shipping_weight'       => $sellingFormatTemplateSource->getItemWeight(),
            'additional_attributes' => $sellingFormatTemplateSource->getAttributes(),
        );

        if ($this->getWalmartListingProduct()->getWpid()) {
            $data['wpid'] = $this->getWalmartListingProduct()->getWpid();
        }

        $startDate = $this->getWalmartListingProduct()->getSellingFormatTemplateSource()->getStartDate();
        if (!empty($startDate)) {
            $data['start_date'] = $startDate;
        } else {
            $data['start_date'] = '1970-01-01 00:00:00';
        }

        $endDate = $this->getWalmartListingProduct()->getSellingFormatTemplateSource()->getEndDate();
        if (!empty($endDate)) {
            $data['end_date'] = $endDate;
        } else {
            $data['end_date'] = '9999-01-01 00:00:00';
        }

        $mustShipAlone = $this->getWalmartListingProduct()->getSellingFormatTemplateSource()->getMustShipAlone();
        if ($mustShipAlone !== null) {
            $data['is_must_ship_alone'] = $mustShipAlone;
        }

        $shipsInOriginalPackaging = $sellingFormatTemplateSource->getShipsInOriginalPackaging();
        if ($shipsInOriginalPackaging !== null) {
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

    protected function getProductData()
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

    protected function getProductIdData()
    {
        if (!isset($this->_cachedData['identifier'])) {
            $this->_cachedData['identifier'] = $this->getIdentifierFromProduct();
        }

        return $this->_cachedData['identifier'];
    }

    private function getIdentifierFromProduct()
    {
        $walmartListingProduct = $this->getListingProduct()->getChildObject();

        if ($identifier = $walmartListingProduct->getGtin()) {
            return array(
                'type' => Ess_M2ePro_Helper_Data::GTIN,
                'id' => $identifier
            );
        }

        if ($identifier = $walmartListingProduct->getUpc()) {
            return array(
                'type' => Ess_M2ePro_Helper_Data::UPC,
                'id' => $identifier
            );
        }

        if ($identifier = $walmartListingProduct->getEan()) {
            return array(
                'type' => Ess_M2ePro_Helper_Data::EAN,
                'id' => $identifier
            );
        }

        if ($identifier = $walmartListingProduct->getIsbn()) {
            return array(
                'type' => Ess_M2ePro_Helper_Data::ISBN,
                'id' => $identifier
            );
        }

        return array();
    }

    // ---------------------------------------

    protected function getDescriptionData()
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

    protected function getMainImageUrl()
    {
        $mainImage = $this->getWalmartListingProduct()->getDescriptionTemplateSource()->getMainImage();

        if ($mainImage === null) {
            return '';
        }

        $walmartConfigurationHelper = Mage::helper('M2ePro/Component_Walmart_Configuration');

        if ($walmartConfigurationHelper->isOptionImagesURLHTTPSMode()) {
            return str_replace('http://', 'https://', $mainImage->getUrl());
        }

        if ($walmartConfigurationHelper->isOptionImagesURLHTTPMode()) {
            return str_replace('https://', 'http://', $mainImage->getUrl());
        }

        return $mainImage->getUrl();
    }

    protected function getProductSecondaryImageUrls()
    {
        $urls = array();

        $walmartConfigurationHelper = Mage::helper('M2ePro/Component_Walmart_Configuration');
        foreach ($this->getWalmartListingProduct()->getDescriptionTemplateSource()->getGalleryImages() as $image) {
            if (!$image->getUrl()) {
                continue;
            }

            if ($walmartConfigurationHelper->isOptionImagesURLHTTPSMode()) {
                $urls[] = str_replace('http://', 'https://', $image->getUrl());
                continue;
            }

            if ($walmartConfigurationHelper->isOptionImagesURLHTTPMode()) {
                $urls[] = str_replace('https://', 'http://', $image->getUrl());
                continue;
            }

            $urls[] = $image->getUrl();
        }

        return $urls;
    }

    protected function getSwatchImages()
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
        if ($image === null) {
            return array();
        }

        $walmartConfigurationHelper = Mage::helper('M2ePro/Component_Walmart_Configuration');
        $url = $image->getUrl();

        if ($walmartConfigurationHelper->isOptionImagesURLHTTPSMode()) {
            $url = str_replace('http://', 'https://', $url);
        }

        if ($walmartConfigurationHelper->isOptionImagesURLHTTPMode()) {
            $url = str_replace('https://', 'http://', $url);
        }

        $swatchImageData = array(
            'url'          => $url,
            'by_attribute' => $swatchAttribute,
        );

        return array($swatchImageData);
    }

    // ---------------------------------------

    protected function getShippingOverrides()
    {
        $result = array();

        $shippingOverrides = $this->getWalmartListingProduct()->getWalmartSellingFormatTemplate()
            ->getShippingOverrides(true);

        if (empty($shippingOverrides)) {
            return $result;
        }

        foreach ($shippingOverrides as $shippingOverride) {
            $source = $shippingOverride->getSource(
                $this->getWalmartListingProduct()->getActualMagentoProduct()
            );

            $result[] = array(
                'ship_method'         => $shippingOverride->getMethod(),
                'ship_region'         => $shippingOverride->getRegion(),
                'ship_price'          => $source->getCost(),
                'is_shipping_allowed' => (bool)$shippingOverride->getIsShippingAllowed(),
            );
        }

        return $result;
    }

    //########################################
}
