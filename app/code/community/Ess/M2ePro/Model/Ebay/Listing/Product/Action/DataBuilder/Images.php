<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Ebay_Template_Description_Source as DescriptionSource;

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_Images
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_Abstract
{
    //########################################

    public function getData()
    {
        $this->searchNotFoundAttributes();

        $links = array();
        $galleryImages = $this->getEbayListingProduct()->getDescriptionTemplateSource()->getGalleryImages();

        foreach ($galleryImages as $image) {
            if (!$image->getUrl()) {
                continue;
            }

            $links[] = $image->getUrl();
        }

        $data = array(
            'gallery_type' => $this->getEbayListingProduct()->getEbayDescriptionTemplate()->getGalleryType(),
            'images'       => $links,
            'supersize'    => $this->getEbayListingProduct()
                ->getEbayDescriptionTemplate()
                ->isUseSupersizeImagesEnabled()
        );

        $this->processNotFoundAttributes('Main Image / Gallery Images');

        $result = array(
            'images' => $data,
        );

        if (!$this->_isVariationItem) {
            return $result;
        }

        $result['variation_image'] = $this->getVariationImage();

        return $result;
    }

    //########################################

    protected function getVariationImage()
    {
        $attributeLabels = array();

        if ($this->getMagentoProduct()->isConfigurableType()) {
            $attributeLabels = $this->getConfigurableImagesAttributeLabels();
        }

        if ($this->getMagentoProduct()->isGroupedType()) {
            $attributeLabels = array(Ess_M2ePro_Model_Magento_Product_Variation::GROUPED_PRODUCT_ATTRIBUTE_LABEL);
        }

        if ($this->getMagentoProduct()->isBundleType()) {
            $attributeLabels = $this->getBundleImagesAttributeLabels();
        }

        if (empty($attributeLabels)) {
            return array();
        }

        return $this->getImagesDataByAttributeLabels($attributeLabels);
    }

    protected function getConfigurableImagesAttributeLabels()
    {
        $descriptionTemplate = $this->getEbayListingProduct()->getEbayDescriptionTemplate();

        if (!$descriptionTemplate->isVariationConfigurableImages()) {
            return array();
        }

        $product = $this->getMagentoProduct()->getProduct();

        $attributeCodes = $descriptionTemplate->getDecodedVariationConfigurableImages();
        $attributes = array();

        foreach ($attributeCodes as $attributeCode) {
            /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            $attribute = $product->getResource()->getAttribute($attributeCode);

            if (!$attribute) {
                continue;
            }

            $attribute->setStoreId($product->getStoreId());
            $attributes[] = $attribute;
        }

        if (empty($attributes)) {
            return array();
        }

        $attributeLabels = array();

        /** @var $productTypeInstance Mage_Catalog_Model_Product_Type_Configurable */
        $productTypeInstance = $this->getMagentoProduct()->getTypeInstance();

        foreach ($productTypeInstance->getConfigurableAttributes() as $configurableAttribute) {

            /** @var $configurableAttribute Mage_Catalog_Model_Product_Type_Configurable_Attribute */
            $configurableAttribute->setStoteId($product->getStoreId());

            foreach ($attributes as $attribute) {
                if ((int)$attribute->getAttributeId() == (int)$configurableAttribute->getAttributeId()) {
                    $attributeLabels = array();
                    foreach ($attribute->getStoreLabels() as $storeLabel) {
                        $attributeLabels[] = trim($storeLabel);
                    }

                    $attributeLabels[] = trim($configurableAttribute->getData('label'));
                    $attributeLabels[] = trim($attribute->getFrontendLabel());

                    $attributeLabels = array_filter($attributeLabels);

                    break 2;
                }
            }
        }

        if (empty($attributeLabels)) {
            $this->addNotFoundAttributesMessages(
                Mage::helper('M2ePro')->__('Change Images for Attribute'),
                $attributes
            );

            return array();
        }

        return $attributeLabels;
    }

    protected function getBundleImagesAttributeLabels()
    {
        $variations = $this->getMagentoProduct()->getVariationInstance()->getVariationsTypeStandard();
        if (!empty($variations['set'])) {
            return array((string)key($variations['set']));
        }

        return array();
    }

    protected function getImagesDataByAttributeLabels(array $attributeLabels)
    {
        $images = array();
        $imagesLinks = array();
        $attributeLabel = false;

        foreach ($this->getListingProduct()->getVariations(true) as $variation) {
            /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */

            if ($variation->getChildObject()->isDelete()) {
                continue;
            }

            foreach ($variation->getOptions(true) as $option) {

                /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */

                $optionLabel = trim($option->getAttribute());
                $optionValue = trim($option->getOption());

                $foundAttributeLabel = false;
                foreach ($attributeLabels as $tempLabel) {
                    if (strtolower($tempLabel) == strtolower($optionLabel)) {
                        $foundAttributeLabel = $optionLabel;
                        break;
                    }
                }

                if ($foundAttributeLabel === false) {
                    continue;
                }

                if (!isset($imagesLinks[$optionValue])) {
                    $imagesLinks[$optionValue] = array();
                }

                $attributeLabel = $foundAttributeLabel;
                $optionImages = $this->getEbayListingProduct()->getEbayDescriptionTemplate()
                    ->getSource($option->getMagentoProduct())
                    ->getVariationImages();

                foreach ($optionImages as $image) {
                    if (!$image->getUrl()) {
                        continue;
                    }

                    if (count($imagesLinks[$optionValue]) >= DescriptionSource::VARIATION_IMAGES_COUNT_MAX) {
                        break 2;
                    }

                    if (!isset($images[$image->getHash()])) {
                        $imagesLinks[$optionValue][] = $image->getUrl();
                        $images[$image->getHash()] = $image;
                    }
                }
            }
        }

        if (!$attributeLabel || !$imagesLinks) {
            return array();
        }

        return array(
            'specific' => $attributeLabel,
            'images'   => $imagesLinks
        );
    }

    //########################################
}
