<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Template_Description_Source
{
    /**
     * @var $magentoProduct Ess_M2ePro_Model_Magento_Product
     */
    private $magentoProduct = null;

    /**
     * @var $descriptionTemplateModel Ess_M2ePro_Model_Template_Description
     */
    private $descriptionTemplateModel = null;

    // ########################################

    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $this->magentoProduct = $magentoProduct;
        return $this;
    }

    public function getMagentoProduct()
    {
        return $this->magentoProduct;
    }

    // ----------------------------------------

    public function setDescriptionTemplate(Ess_M2ePro_Model_Template_Description $instance)
    {
        $this->descriptionTemplateModel = $instance;
        return $this;
    }

    public function getDescriptionTemplate()
    {
        return $this->descriptionTemplateModel;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Description
     */
    public function getAmazonDescriptionTemplate()
    {
        return $this->getDescriptionTemplate()->getChildObject();
    }

    // ########################################

    public function getWorldwideId()
    {
        $result = '';
        $src = $this->getAmazonDescriptionTemplate()->getWorldwideIdSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_Description::WORLDWIDE_ID_MODE_NONE) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_Description::WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        is_string($result) && $result = trim($result);

        return $result;
    }

    public function getItemPackageQuantity()
    {
        $result = '';
        $src = $this->getAmazonDescriptionTemplate()->getItemPackageQuantitySource();

        if ($this->getAmazonDescriptionTemplate()->isItemPackageQuantityModeNone()) {
            $result = NULL;
        }

        if ($this->getAmazonDescriptionTemplate()->isItemPackageQuantityModeCustomValue()) {
            $result = (int)$src['value'];
        }

        if ($this->getAmazonDescriptionTemplate()->isItemPackageQuantityModeCustomAttribute()) {
            $result = (int)$this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $result;
    }

    public function getNumberOfItems()
    {
        $result = '';
        $src = $this->getAmazonDescriptionTemplate()->getNumberOfItemsSource();

        if ($this->getAmazonDescriptionTemplate()->isNumberOfItemsModeNone()) {
            $result = NULL;
        }

        if ($this->getAmazonDescriptionTemplate()->isNumberOfItemsModeCustomValue()) {
            $result = (int)$src['value'];
        }

        if ($this->getAmazonDescriptionTemplate()->isNumberOfItemsModeCustomAttribute()) {
            $result = (int)$this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $result;
    }

    // ########################################
}